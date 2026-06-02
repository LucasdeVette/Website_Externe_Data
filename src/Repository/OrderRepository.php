<?php

namespace App\Repository;

use App\Database;
use App\Model\Order;
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
        $orders = [];
        foreach ($stmt->fetchAll() as $row) {
            $order = new Order($row);
            $order->setItems($this->getItems($order->getId()));
            $orders[] = $order;
        }
        return $orders;
    }

    public function findById(int $id): ?Order
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, s.name AS supplier_name
             FROM orders o
             LEFT JOIN suppliers s ON o.supplier_id = s.id
             WHERE o.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $order = new Order($row);
        $order->setItems($this->getItems($id));
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
}
