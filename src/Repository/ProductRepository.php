<?php

namespace App\Repository;

use App\Model\Product;
use App\Model\Category;
use App\Model\Supplier;

class ProductRepository extends BaseRepository
{
    public function findAll(?string $search = null, ?string $sort = null, ?int $categoryId = null): array
    {
        $sql = 'SELECT p.*, c.name AS category_name, s.name AS supplier_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE 1=1';
        $params = [];

        if ($categoryId) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }

        if ($search) {
            $sql .= ' AND (p.name LIKE ? OR p.barcode LIKE ? OR p.description LIKE ?)';
            $term = '%' . $search . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        switch ($sort) {
            case 'price_asc':  $sql .= ' ORDER BY p.price ASC'; break;
            case 'price_desc': $sql .= ' ORDER BY p.price DESC'; break;
            case 'stock_asc':  $sql .= ' ORDER BY p.stock ASC'; break;
            case 'stock_desc': $sql .= ' ORDER BY p.stock DESC'; break;
            case 'name_desc':  $sql .= ' ORDER BY p.name DESC'; break;
            default:           $sql .= ' ORDER BY p.name ASC'; break;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $products = [];
        foreach ($stmt->fetchAll() as $row) {
            $products[] = $this->hydrate($row);
        }
        return $products;
    }

    public function findById(int $id): ?Product
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, c.name AS category_name, s.name AS supplier_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN suppliers s ON p.supplier_id = s.id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function findByBarcode(string $barcode): ?Product
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, c.name AS category_name, s.name AS supplier_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN suppliers s ON p.supplier_id = s.id
             WHERE p.barcode = ?'
        );
        $stmt->execute([$barcode]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function getLowStock(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, c.name AS category_name, s.name AS supplier_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN suppliers s ON p.supplier_id = s.id
             WHERE p.stock <= p.min_stock
             ORDER BY p.stock ASC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
        $products = [];
        foreach ($stmt->fetchAll() as $row) {
            $products[] = $this->hydrate($row);
        }
        return $products;
    }

    public function create(Product $product): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO products (category_id, supplier_id, name, description, price, stock, min_stock, barcode, image_url, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $product->getCategoryId(),
            $product->getSupplierId(),
            $product->getName(),
            $product->getDescription(),
            $product->getPrice(),
            $product->getStock(),
            $product->getMinStock(),
            $product->getBarcode(),
            $product->getImageUrl(),
            $product->isActive() ? 1 : 0,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(Product $product): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE products SET category_id=?, supplier_id=?, name=?, description=?, price=?, stock=?, min_stock=?, barcode=?, image_url=?, is_active=?
             WHERE id=?'
        );
        return $stmt->execute([
            $product->getCategoryId(),
            $product->getSupplierId(),
            $product->getName(),
            $product->getDescription(),
            $product->getPrice(),
            $product->getStock(),
            $product->getMinStock(),
            $product->getBarcode(),
            $product->getImageUrl(),
            $product->isActive() ? 1 : 0,
            $product->getId(),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM products WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function countLowStock(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM products WHERE stock <= min_stock');
        return (int) $stmt->fetchColumn();
    }

    public function countTotal(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM products');
        return (int) $stmt->fetchColumn();
    }

    public function countOutOfStock(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM products WHERE stock = 0');
        return (int) $stmt->fetchColumn();
    }

    public function getPriceComparison(?int $categoryId = null): array
    {
        $sql = 'SELECT p.*, c.name AS category_name, s.name AS supplier_name,
                       pc.avg_price, pc.min_price, pc.max_price
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN (
                    SELECT category_id,
                           AVG(price) AS avg_price,
                           MIN(price) AS min_price,
                           MAX(price) AS max_price
                    FROM products
                    GROUP BY category_id
                ) pc ON p.category_id = pc.category_id
                WHERE 1=1';
        $params = [];

        if ($categoryId) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }

        $sql .= ' ORDER BY c.name, p.name';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $product = $this->hydrate($row);
            $results[] = [
                'product' => $product,
                'avg_price' => (float) $row['avg_price'],
                'min_price' => (float) $row['min_price'],
                'max_price' => (float) $row['max_price'],
                'diff_percent' => $row['avg_price'] > 0
                    ? round((($product->getPrice() - (float) $row['avg_price']) / (float) $row['avg_price']) * 100, 1)
                    : 0,
            ];
        }
        return $results;
    }

    public function sumStockValue(): float
    {
        $stmt = $this->pdo->query('SELECT COALESCE(SUM(price * stock), 0) FROM products');
        return (float) $stmt->fetchColumn();
    }

    private function hydrate(array $row): Product
    {
        $product = new Product($row);
        $product->setIsActive((bool) ($row['is_active'] ?? true));

        if (!empty($row['category_name'])) {
            $category = new Category([
                'id' => $row['category_id'],
                'name' => $row['category_name'],
            ]);
            $product->setCategory($category);
        }

        if (!empty($row['supplier_name'])) {
            $supplier = new Supplier([
                'id' => $row['supplier_id'] ?? 0,
                'name' => $row['supplier_name'],
            ]);
            $product->setSupplier($supplier);
        }

        return $product;
    }
}
