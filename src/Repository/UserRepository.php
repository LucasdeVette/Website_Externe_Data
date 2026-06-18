
<?php

namespace App;

use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function create(string $username, string $password, string $displayName, string $email): bool
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, password_hash, display_name, email)
            VALUES (?, ?, ?, ?)
        ");

        return $stmt->execute([$username, $passwordHash, $displayName, $email]);
    }
}