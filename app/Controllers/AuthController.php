<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Student;
use App\Models\AccessLog;

class AuthController extends Controller {

    private $studentModel;

    public function __construct() {
        $this->studentModel = new Student();
    }

    // --- LOGIN NORMAL ---

    public function showLogin() {
        if (isset($_SESSION['aluno_id'])) $this->redirect('/dashboard');
        $this->view('student/login', ['titulo' => 'Login ETECast']);
    }

    public function doLogin() {
        $matricula = $_POST['matricula'] ?? '';
        $senha = $_POST['senha'] ?? '';

        $aluno = $this->studentModel->findByMatricula($matricula);

        if ($aluno && $aluno['password_hash'] && password_verify($senha, $aluno['password_hash'])) {
            if ($aluno['ativo'] == 0) {
                $this->view('student/login', ['erro' => 'Usuário bloqueado.']);
                return;
            }
            
            // Sucesso!
            session_regenerate_id(true); 
            unset($_SESSION['aluno_id']);
            $_SESSION['aluno_id'] = $aluno['id'];
            $_SESSION['aluno_nome'] = $aluno['nome'];
            
            // --- LOG DE LOGIN ---
            try {
                $log = new AccessLog();
                $ip = $_SERVER['REMOTE_ADDR'];
                $agente = $_SERVER['HTTP_USER_AGENT'];
                $log->log($aluno['id'], null, 'login', $ip, $agente);
            } catch (\Exception $e) {
                error_log("Falha ao registrar log de login: " . $e->getMessage());
            }

            $this->redirect('/dashboard');
        } else {
            $this->view('student/login', ['erro' => 'Matrícula ou senha inválida.']);
        }
    }

    // --- PRIMEIRO ACESSO / CADASTRO DE SENHA ---

    public function showRegister() {
        $this->view('student/register', ['titulo' => 'Primeiro Acesso']);
    }

    public function doRegisterCheck() {
        $matricula = $_POST['matricula'] ?? '';
        $data_nasc = $_POST['data_nascimento'] ?? '';

        $aluno = $this->studentModel->findByMatricula($matricula);

        if ($aluno && $aluno['data_nascimento'] === $data_nasc) {
            if ($aluno['password_hash'] !== null) {
                $this->view('student/register', ['erro' => 'Você já possui uma senha. Use a tela de login.']);
                return;
            }
            
            $_SESSION['student_id_to_register'] = $aluno['id'];
            $this->redirect('/create-password');
        } else {
            $this->view('student/register', ['erro' => 'Matrícula ou Data de Nascimento inválida.']);
        }
    }

    public function showCreatePassword() {
        if (!isset($_SESSION['student_id_to_register'])) {
            $this->redirect('/register');
        }
        $this->view('student/create_password', ['titulo' => 'Criar sua Senha']);
    }

    public function doCreatePassword() {
        if (!isset($_SESSION['student_id_to_register'])) {
            $this->redirect('/register');
        }

        $senha = $_POST['senha'] ?? '';
        $senha_confirm = $_POST['senha_confirm'] ?? '';

        if (empty($senha) || $senha !== $senha_confirm) {
            $this->view('student/create_password', ['erro' => 'As senhas não conferem.']);
            return;
        }
        
        if (strlen($senha) < 6) {
             $this->view('student/create_password', ['erro' => 'A senha deve ter no mínimo 6 caracteres.']);
            return;
        }

        $id = $_SESSION['student_id_to_register'];
        $this->studentModel->setPassword($id, $senha);

        unset($_SESSION['student_id_to_register']);
        $this->view('student/login', ['sucesso' => 'Senha criada! Faça seu login.']);
    }

    // --- LOGOUT ---

    public function logout() {
        session_destroy();
        $this->redirect('/');
    }
}