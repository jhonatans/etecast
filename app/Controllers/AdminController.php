<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Admin;
use App\Models\Content;
use App\Models\AccessLog;

class AdminController extends Controller {

    /**
     * Esta função privada verifica se o admin está logado.
     */
    private function checkAuth() {
        if (!isset($_SESSION['admin_id'])) {
            $this->redirect('/admin/login');
            exit; 
        }
    }

    // --- AUTENTICAÇÃO ---

    public function showLogin() {
        if (isset($_SESSION['admin_id'])) {
            $this->redirect('/admin/dashboard');
        }
        $this->view('admin/login', ['titulo' => 'Login Admin']);
    }

    public function doLogin() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $adminModel = new Admin();
        $admin = $adminModel->findByUsername($username);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            unset($_SESSION['aluno_id']);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $this->redirect('/admin/dashboard');
        } else {
            $this->view('admin/login', ['erro' => 'Usuário ou senha inválido.']);
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/admin/login');
    }

    // --- DASHBOARD ---

    public function dashboard() {
        $this->checkAuth();
        $this->redirect('/admin/content');
    }

    // --- CRUD DE CONTEÚDO (Upload) ---

    public function listContent() {
        $this->checkAuth(); 
        $contentModel = new Content();
        $this->view('admin/content_list', [
            'titulo' => 'Gerenciar Conteúdo',
            'conteudos' => $contentModel->findAllVisible()
        ]);
    }

    public function showAddContent() {
        $this->checkAuth();
        $this->view('admin/content_form', ['titulo' => 'Adicionar Conteúdo']);
    }

    // Esta que trata os 2 uploads (mídia e capa)
    public function doAddContent() {
        $this->checkAuth();
        
        $titulo = $_POST['titulo'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $adminId = $_SESSION['admin_id'];
        
        // Arquivos
        $arquivo_midia = $_FILES['arquivo'] ?? null;
        $arquivo_cover = $_FILES['cover'] ?? null; 

        if (!$titulo || !$tipo || !$arquivo_midia || $arquivo_midia['error'] !== UPLOAD_ERR_OK) {
            $this->view('admin/content_form', ['erro' => 'Título, Tipo e Arquivo de Mídia são obrigatórios.']);
            return;
        }

        // --- 1. UPLOAD DA MÍDIA ---
        $fileName_midia = basename($arquivo_midia['name']);
        $fileTmp_midia = $arquivo_midia['tmp_name'];
        $subDir_midia = $tipo; 
        
        $uploadPath_midia = BASE_PATH . "/public/media/$subDir_midia/" . $fileName_midia;
        $dbPath_midia = "$subDir_midia/" . $fileName_midia;

        if (!move_uploaded_file($fileTmp_midia, $uploadPath_midia)) {
             $this->view('admin/content_form', ['erro' => 'Falha ao mover o arquivo de mídia. Verifique permissões.']);
             return;
        }

        // --- 2. UPLOAD DA CAPA ---
        $dbPath_cover = null;
        
        if ($arquivo_cover && $arquivo_cover['error'] === UPLOAD_ERR_OK) {
            $fileName_cover = basename($arquivo_cover['name']);
            $fileTmp_cover = $arquivo_cover['tmp_name'];
            $subDir_cover = 'covers';

            $uploadDir_cover = BASE_PATH . "/public/media/$subDir_cover/";
            if (!is_dir($uploadDir_cover)) {
                mkdir($uploadDir_cover, 0775, true);
            }

            $uploadPath_cover = $uploadDir_cover . $fileName_cover;
            $dbPath_cover = "$subDir_cover/" . $fileName_cover;

            if (!move_uploaded_file($fileTmp_cover, $uploadPath_cover)) {
                error_log("Falha ao mover o arquivo de capa: " . $fileName_cover);
                $dbPath_cover = null;
            }
        }

        // --- 3. SALVAR NO BANCO ---
        $contentModel = new Content();
        $contentModel->create($tipo, $titulo, $descricao, $dbPath_midia, $dbPath_cover, $adminId);
        
        $this->redirect('/admin/content');
    }

    // --- LOGS ---

    public function listLogs() {
        $this->checkAuth(); 
        $logModel = new AccessLog();
        $this->view('admin/logs', [
            'titulo' => 'Logs de Acesso',
            'logs' => $logModel->listLogs(100)
        ]);
    }
}
