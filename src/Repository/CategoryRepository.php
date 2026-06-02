<?php

namespace App\Repository;

use App\Model\Category;

class CategoryRepository extends BaseRepository
{
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM categories ORDER BY name');
        $categories = [];
        foreach ($stmt->fetchAll() as $row) {
            $categories[] = new Category($row);
        }
        return $categories;
    }

    public function findById(int $id): ?Category
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new Category($row) : null;
    }

    public function create(Category $category): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO categories (name, description) VALUES (?, ?)'
        );
        $stmt->execute([
            $category->getName(),
            $category->getDescription(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(Category $category): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE categories SET name = ?, description = ? WHERE id = ?'
        );
        return $stmt->execute([
            $category->getName(),
            $category->getDescription(),
            $category->getId(),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function productCount(int $id): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }
}
