<?php

namespace App\Repository;

use App\Database;
use App\Model\Supplier;
use PDO;

class SupplierRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM suppliers ORDER BY name');
        $list = [];
        foreach ($stmt->fetchAll() as $row) {
            $list[] = new Supplier($row);
        }
        return $list;
    }

    public function findById(int $id): ?Supplier
    {
        $stmt = $this->pdo->prepare('SELECT * FROM suppliers WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new Supplier($row) : null;
    }

    public function create(Supplier $supplier): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $supplier->getName(),
            $supplier->getContactPerson(),
            $supplier->getEmail(),
            $supplier->getPhone(),
            $supplier->getAddress(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(Supplier $supplier): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE suppliers SET name=?, contact_person=?, email=?, phone=?, address=? WHERE id=?'
        );
        return $stmt->execute([
            $supplier->getName(),
            $supplier->getContactPerson(),
            $supplier->getEmail(),
            $supplier->getPhone(),
            $supplier->getAddress(),
            $supplier->getId(),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM suppliers WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function productCount(int $id): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM products WHERE supplier_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }
}
