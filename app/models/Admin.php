<?php
namespace app\models;

use PDO;

class Admin {
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Encontra um administrador pelo username.
     * @param string $username
     * @return array|false
     */
    public function findByUsername($username) {
        $stmt = $this->pdo->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Adiciona um novo administrador.
     * @param string $username
     * @param string $password
     * @param string $email
     * @return bool
     */
    public function create($username, $password, $email) {
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        $stmt = $this->pdo->prepare('INSERT INTO admins (username, password_hash, email) VALUES (?, ?, ?)');
        return $stmt->execute([$username, $passwordHash, $email]);
    }
}