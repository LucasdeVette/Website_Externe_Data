<?php

namespace App\Service;

use App\Database;
use PDO;

class AuthService
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function login(string $username, string $password): bool
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['display_name'] = $user['display_name'];
            return true;
        }

        return false;
    }

//Lucas (functie registreren)
public function register(string $username, string $password, string $displayName, string $email): bool
{
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $this->pdo->prepare(
        'INSERT INTO users (username, password_hash, display_name, email)
         VALUES (?, ?, ?, ?)'
    );

    return $stmt->execute([$username, $passwordHash, $displayName, $email]);
}
//
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
