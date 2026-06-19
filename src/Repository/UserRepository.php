<?php

namespace App\Repository;

use App\Model\User;

class UserRepository extends BaseRepository
{
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users ORDER BY display_name');
        $list = [];
        foreach ($stmt->fetchAll() as $row) {
            $list[] = new User($row);
        }
        return $list;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new User($row) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ? new User($row) : null;
    }

    public function create(User $user): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, password_hash, display_name, email) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $user->getUsername(),
            $user->getPasswordHash(),
            $user->getDisplayName(),
            $user->getEmail(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(User $user): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET username=?, display_name=?, email=? WHERE id=?'
        );
        return $stmt->execute([
            $user->getUsername(),
            $user->getDisplayName(),
            $user->getEmail(),
            $user->getId(),
        ]);
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash=? WHERE id=?');
        return $stmt->execute([$passwordHash, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
