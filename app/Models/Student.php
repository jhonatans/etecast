<?php

namespace App\Models;

class Student extends BaseModel {

    /**
     * Busca um aluno pela matrícula
     */
    public function findByMatricula(string $matricula) {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE matricula = ?");
        $stmt->execute([$matricula]);
        return $stmt->fetch();
    }

    /**
     * Define a senha para um aluno (primeiro acesso)
     */
    public function setPassword(int $id, string $password) {
        $hash = password_hash($password, PASSWORD_ARGON2ID);
        
        $stmt = $this->db->prepare("UPDATE students SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }
}