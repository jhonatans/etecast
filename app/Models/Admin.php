<?php

namespace App\Models;

require_once 'BaseModel.php'; 

class Admin extends BaseModel {
    
    public function findByUsername(string $username) {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
}