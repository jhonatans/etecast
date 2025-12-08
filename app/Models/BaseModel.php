<?php

namespace App\Models;

use PDO;

class BaseModel {
    protected $db;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
    }
}