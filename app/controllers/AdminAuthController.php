<?php
namespace app\controllers;

use app\models\Admin;
use PDO;

class AdminAuthController {
    protected $adminModel;

    public function __construct(PDO $pdo) {
        $this->adminModel = new Admin($pdo);
    }

    public function showLogin() {
        // Exibe o formulário de login do administrador
        $error = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_error']);
        require __DIR__ . '/../views/admin/login.php';
    }

    public function login($data) {
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        $admin = $this->adminModel->findByUsername($username);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: ' . BASE_URL . '/admin');
            exit();
        } else {
            $_SESSION['admin_error'] = 'Usuário ou senha inválidos.';
            header('Location: ' . BASE_URL . '/admin/login');
            exit();
        }
    }

    public function logout() {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        session_destroy();
        header('Location: ' . BASE_URL . '/admin/login');
        exit();
    }
}