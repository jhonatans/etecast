<?php
namespace app\models;
use PDO;

class Student {
    protected $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findByMatricula($matricula) {
        $stmt = $this->pdo->prepare('SELECT * FROM students WHERE matricula = ? AND ativo = 1 LIMIT 1');
        $stmt->execute([$matricula]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM students WHERE id = ? AND ativo = 1 LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function validateMatriculaAndBirth($matricula, $data_nasc_Ymd) {
        $s = $this->findByMatricula($matricula);
        if (!$s) return false;
        return ($s['data_nascimento'] === $data_nasc_Ymd) ? $s : false;
    }

    public function setPassword($id, $password) {
        $hash = password_hash($password, PASSWORD_ARGON2ID);
        $stmt = $this->pdo->prepare('UPDATE students SET password_hash = ? WHERE id = ?');
        return $stmt->execute([$hash, $id]);
    }

    public function verifyPassword($matricula, $password) {
        $s = $this->findByMatricula($matricula);
        if (!$s || empty($s['password_hash'])) return false;
        return password_verify($password, $s['password_hash']) ? $s : false;
    }
}