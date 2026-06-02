<?php

namespace App\Repository;

use App\Database;
use App\Model\Order;
use App\Model\Supplier;
use PDO;

class OrderRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function findAll(?string $status = null): array
    {
        $sql = 'SELECT o.*, s.name AS supplier_name
                FROM orders o
                LEFT JOIN suppliers s ON o.supplier_id = s.id
                WHERE 1=1';
        $params = [];

        if ($status) {
            $sql .= ' AND o.status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY o.order_date DESC, o.created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            return [];
        }

        $orders = [];
        $ids = [];
        foreach ($rows as $row) {
            $orders[$row['id']] = new Order($row);
            $ids[] = $row['id'];
        }

        $this->loadItems($orders, $ids);

        return array_values($orders);
    }

    public function findRecent(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, s.name AS supplier_name
             FROM orders o
             LEFT JOIN suppliers s ON o.supplier_id = s.id
             ORDER BY o.order_date DESC, o.created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            return [];
        }

        $orders = [];
        $ids = [];
        foreach ($rows as $row) {
            $orders[$row['id']] = new Order($row);
            $ids[] = $row['id'];
        }

        $this->loadItems($orders, $ids);

        return array_values($orders);
    }

    public function findById(int $id): ?Order
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, s.name AS supplier_name,
                    s.contact_person, s.email, s.phone, s.address
             FROM orders o
             LEFT JOIN suppliers s ON o.supplier_id = s.id
             WHERE o.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $order = new Order($row);
        $order->setItems($this->getItems($id));

        if ($row['supplier_id'] && $row['supplier_name']) {
            $order->setSupplier(new Supplier([
                'id'             => $row['supplier_id'],
                'name'           => $row['supplier_name'],
                'contact_person' => $row['contact_person'],
                'email'          => $row['email'],
                'phone'          => $row['phone'],
                'address'        => $row['address'],
            ]));
        }

        return $order;
    }

    public function getItems(int $orderId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT oi.*, p.name AS product_name
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?
             ORDER BY p.name'
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function create(Order $order): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO orders (supplier_id, order_date, status, notes) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $order->getSupplierId(),
                $order->getOrderDate(),
                $order->getStatus(),
                $order->getNotes(),
            ]);
            $orderId = (int) $this->pdo->lastInsertId();

            foreach ($order->getItems() as $item) {
                $this->addItem($orderId, $item);
            }

            $this->pdo->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function addItem(int $orderId, array $item): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['quantity'],
            $item['unit_price'],
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM orders WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM orders WHERE status = ?');
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }

    public function countTotal(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM orders');
        return (int) $stmt->fetchColumn();
    }

    private function loadItems(array &$orders, array $ids): void
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT oi.*, p.name AS product_name
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id IN ($placeholders)
             ORDER BY p.name"
        );
        $stmt->execute($ids);
        foreach ($stmt->fetchAll() as $item) {
            $orders[$item['order_id']]->addItem($item);
        }
    }
}
