<?php
namespace app\controllers;

use app\models\Admin;
use PDO;

class AdminController {
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function dashboard() {
        // (Middleware já protegeu esta rota)
        $adminUsername = $_SESSION['admin_username'] ?? 'Admin';
        echo "<h1>Dashboard do Admin</h1>";
        echo "Bem-vindo, " . htmlspecialchars($adminUsername);
        echo '<br><a href="/admin/content/upload">Fazer Upload</a>';
        echo '<br><a href="/admin/logout">Sair</a>';
    }

}