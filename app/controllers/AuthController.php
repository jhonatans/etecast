<?php
namespace app\controllers;

use app\models\Student;
use PDO;

class AuthController {
    protected $studentModel;
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->studentModel = new Student($pdo);
    }

    // Exibe o formulário de login
    public function showLogin() {
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);
        // Precisaremos criar esta view em breve
        // require __DIR__ . '/../views/student/login.php';
        echo '<h1>Login Aluno</h1><form method="POST"><input name="matricula" placeholder="Matrícula"><input name="data_nascimento" placeholder="DD/MM/AAAA (1 Acesso)" type="text"><input name="password" placeholder="Senha (2 Acesso)" type="password"><button>Entrar</button></form>';
        if($error) echo "<p style='color:red;'>$error</p>";
    }

    // Processa o login
    public function login($data) {
        $matricula = trim($data['matricula'] ?? '');
        $data_nasc = trim($data['data_nascimento'] ?? '');
        $password = trim($data['password'] ?? '');

        $student = $this->studentModel->findByMatricula($matricula);

        if (!$student) {
            $_SESSION['error'] = 'Matrícula não encontrada ou inativa.';
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        // 1. Fluxo de PRIMEIRO ACESSO (Matrícula + Data de Nascimento)
        if (!empty($data_nasc) && empty($password)) {
            $date = \DateTime::createFromFormat('d/m/Y', $data_nasc);
            if (!$date) {
                $_SESSION['error'] = 'Formato da data de nascimento inválido. Use DD/MM/AAAA.';
                header('Location: ' . BASE_URL . '/login'); exit;
            }
            $data_nasc_Ymd = $date->format('Y-m-d');

            if ($this->studentModel->validateMatriculaAndBirth($matricula, $data_nasc_Ymd)) {
                if (empty($student['password_hash'])) {
                    $_SESSION['temp_student_id'] = $student['id'];
                    header('Location: ' . BASE_URL . '/register-password'); exit;
                } else {
                    $_SESSION['error'] = 'Você já tem uma senha. Use o campo "Senha" para entrar.';
                    header('Location: ' . BASE_URL . '/login'); exit;
                }
            } else {
                $_SESSION['error'] = 'Data de nascimento não confere com a matrícula.';
                header('Location: ' . BASE_URL . '/login'); exit;
            }
        }
        
        // 2. Fluxo de LOGIN NORMAL (Matrícula + Senha)
        if (!empty($password)) {
            $validStudent = $this->studentModel->verifyPassword($matricula, $password);
            if ($validStudent) {
                session_regenerate_id(true);
                $_SESSION['student_id'] = $validStudent['id'];
                $_SESSION['student_nome'] = $validStudent['nome'];
                header('Location: ' . BASE_URL . '/'); exit;
            } else {
                $_SESSION['error'] = 'Senha incorreta.';
                header('Location: ' . BASE_URL . '/login'); exit;
            }
        }

        $_SESSION['error'] = 'Use a Data de Nascimento (1º acesso) ou a Senha (demais acessos).';
        header('Location: ' . BASE_URL . '/login'); exit;
    }
    
    // Exibe o formulário de registro de senha
    public function showRegisterPassword() {
        if (!isset($_SESSION['temp_student_id'])) {
             header('Location: ' . BASE_URL . '/login'); exit;
        }
        // Precisaremos criar esta view em breve
        // require __DIR__ . '/../views/student/register_password.php';
        echo '<h1>Criar Senha (Primeiro Acesso)</h1><form method="POST"><input name="password" placeholder="Nova Senha (min 8 caracteres)" type="password"><input name="password_confirm" placeholder="Confirmar Senha" type="password"><button>Salvar Senha</button></form>';
    }

    // Salva a nova senha
    public function registerPassword($data) {
        if (!isset($_SESSION['temp_student_id'])) {
            header('Location: ' . BASE_URL . '/login'); exit;
        }

        $password = $data['password'] ?? '';
        $password_confirm = $data['password_confirm'] ?? '';

        if (strlen($password) < 8) {
            echo "Senha muito curta. Mínimo 8 caracteres."; exit;
        }
        if ($password !== $password_confirm) {
            echo "As senhas não conferem."; exit; 
        }

        $studentId = $_SESSION['temp_student_id'];
        $this->studentModel->setPassword($studentId, $password);
        
        unset($_SESSION['temp_student_id']);
        
        $student = $this->studentModel->findById($studentId);
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_nome'] = $student['nome'];
        
        header('Location: ' . BASE_URL . '/'); exit;
    }

    // Faz logout
    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit();
    }
}