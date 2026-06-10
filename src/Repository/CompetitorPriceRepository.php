<?php

namespace App\Repository;

use App\Model\CompetitorPrice;
use App\Model\CompetitorStore;

class CompetitorPriceRepository extends BaseRepository
{
    public function findStores(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM competitor_stores ORDER BY name');
        $list = [];
        foreach ($stmt->fetchAll() as $row) {
            $list[] = new CompetitorStore($row);
        }
        return $list;
    }

    public function findStoreById(int $id): ?CompetitorStore
    {
        $stmt = $this->pdo->prepare('SELECT * FROM competitor_stores WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new CompetitorStore($row) : null;
    }

    public function createStore(CompetitorStore $store): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO competitor_stores (name, website) VALUES (?, ?)'
        );
        $stmt->execute([$store->getName(), $store->getWebsite()]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateStore(CompetitorStore $store): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE competitor_stores SET name=?, website=? WHERE id=?'
        );
        return $stmt->execute([$store->getName(), $store->getWebsite(), $store->getId()]);
    }

    public function deleteStore(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM competitor_stores WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function findPrices(int $productId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT cp.*, cs.name AS store_name
             FROM competitor_prices cp
             JOIN competitor_stores cs ON cp.store_id = cs.id
             WHERE cp.product_id = ?
             ORDER BY cs.name'
        );
        $stmt->execute([$productId]);
        $list = [];
        foreach ($stmt->fetchAll() as $row) {
            $cp = new CompetitorPrice($row);
            $cp->setStore(new CompetitorStore([
                'id'   => $row['store_id'],
                'name' => $row['store_name'],
            ]));
            $list[] = $cp;
        }
        return $list;
    }

    public function findLatestPrice(int $productId, int $storeId): ?CompetitorPrice
    {
        $stmt = $this->pdo->prepare(
            'SELECT cp.*, cs.name AS store_name
             FROM competitor_prices cp
             JOIN competitor_stores cs ON cp.store_id = cs.id
             WHERE cp.product_id = ? AND cp.store_id = ?
             ORDER BY cp.recorded_at DESC, cp.id DESC
             LIMIT 1'
        );
        $stmt->execute([$productId, $storeId]);
        $row = $stmt->fetch();
        if ($row) {
            $cp = new CompetitorPrice($row);
            $cp->setStore(new CompetitorStore([
                'id'   => $row['store_id'],
                'name' => $row['store_name'],
            ]));
            return $cp;
        }
        return null;
    }

    public function savePrice(CompetitorPrice $price): int
    {
        $existing = $this->findLatestPrice($price->getProductId(), $price->getStoreId());
        if ($existing && $existing->getRecordedAt() === $price->getRecordedAt()) {
            $stmt = $this->pdo->prepare(
                'UPDATE competitor_prices SET price=?, updated_at=NOW() WHERE id=?'
            );
            $stmt->execute([$price->getPrice(), $existing->getId()]);
            return $existing->getId();
        }
        $stmt = $this->pdo->prepare(
            'INSERT INTO competitor_prices (product_id, store_id, price, recorded_at) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$price->getProductId(), $price->getStoreId(), $price->getPrice(), $price->getRecordedAt()]);
        return (int) $this->pdo->lastInsertId();
    }

    public function deletePrice(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM competitor_prices WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function getComparisonForProduct(int $productId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT cp.*, cs.name AS store_name
             FROM competitor_prices cp
             JOIN competitor_stores cs ON cp.store_id = cs.id
             WHERE cp.product_id = ?
               AND cp.recorded_at = (
                   SELECT MAX(cp2.recorded_at)
                   FROM competitor_prices cp2
                   WHERE cp2.product_id = cp.product_id AND cp2.store_id = cp.store_id
               )
             ORDER BY cs.name'
        );
        $stmt->execute([$productId]);
        $list = [];
        foreach ($stmt->fetchAll() as $row) {
            $cp = new CompetitorPrice($row);
            $cp->setStore(new CompetitorStore([
                'id'   => $row['store_id'],
                'name' => $row['store_name'],
            ]));
            $list[] = $cp;
        }
        return $list;
    }

    public function getAllComparisons(?int $categoryId = null): array
    {
        $stores = $this->findStores();

        $sql = 'SELECT p.id AS product_id, p.category_id, p.name AS product_name,
                       p.price AS our_price, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 1=1';
        $params = [];

        if ($categoryId) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }

        $sql .= ' ORDER BY c.name, p.name';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $pid = $row['product_id'];
            $grouped[$pid] = [
                'product_id'   => $pid,
                'category_id'  => (int) $row['category_id'],
                'product_name' => $row['product_name'],
                'our_price'    => (float) $row['our_price'],
                'category_name' => $row['category_name'],
                'competitors'  => [],
            ];
        }

        if (empty($stores) || empty($grouped)) {
            return array_values($grouped);
        }

        $storeIds = array_map(fn($s) => $s->getId(), $stores);
        $placeholders = implode(',', array_fill(0, count($storeIds), '?'));

        $priceSql = "SELECT cp.product_id, cp.store_id, cp.price, cp.recorded_at
                     FROM competitor_prices cp
                     INNER JOIN (
                         SELECT product_id, store_id, MAX(recorded_at) AS max_date
                         FROM competitor_prices
                         GROUP BY product_id, store_id
                     ) latest ON cp.product_id = latest.product_id
                             AND cp.store_id = latest.store_id
                             AND cp.recorded_at = latest.max_date
                     WHERE cp.store_id IN ($placeholders)";

        $priceStmt = $this->pdo->prepare($priceSql);
        $priceStmt->execute($storeIds);
        $priceRows = $priceStmt->fetchAll();

        $priceMap = [];
        foreach ($priceRows as $pr) {
            $priceMap[$pr['product_id'] . '_' . $pr['store_id']] = $pr;
        }

        foreach ($grouped as $pid => &$g) {
            foreach ($stores as $s) {
                $key = $pid . '_' . $s->getId();
                if (isset($priceMap[$key])) {
                    $pr = $priceMap[$key];
                    $g['competitors'][] = [
                        'store_id'   => $s->getId(),
                        'store_name' => $s->getName(),
                        'price'      => (float) $pr['price'],
                        'diff'       => $g['our_price'] > 0
                            ? round((((float) $pr['price'] - $g['our_price']) / $g['our_price']) * 100, 1)
                            : 0,
                    ];
                } else {
                    $g['competitors'][] = [
                        'store_id'   => $s->getId(),
                        'store_name' => $s->getName(),
                        'price'      => null,
                        'diff'       => null,
                    ];
                }
            }
        }
        unset($g);

        return array_values($grouped);
    }
}
