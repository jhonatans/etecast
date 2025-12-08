<?php
namespace App\Models;

class User extends BaseModel {
    // Lista todos os usuários (para o admin gerenciar)
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM students ORDER BY nome ASC");
        return $stmt->fetchAll();
    }

    // Cria um novo usuário (Professor, Aluno ou Especial)
    public function create($matricula, $nome, $data_nasc, $role) {
        $sql = "INSERT INTO students (matricula, nome, data_nascimento, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$matricula, $nome, $data_nasc, $role]);
    }

    // Remove usuário
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Reseta senha (para padrão ou nula para recadastro)
    public function resetPassword($id) {
        $stmt = $this->db->prepare("UPDATE students SET password_hash = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }
}