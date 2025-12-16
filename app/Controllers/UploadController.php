<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Content;

class UploadController extends Controller {

    // Exibe o formulário (rota GET /creator/upload)
    public function showForm() {
        // Segurança: Só Professor ou Especial
        if (!isset($_SESSION['aluno_role']) || !in_array($_SESSION['aluno_role'], ['professor', 'special'])) {
             $this->redirect('/dashboard'); 
             return;
        }

        // Reusa o formulário do admin
        $this->view('admin/content_form', ['titulo' => 'Área do Criador - Upload']);
    }

    // Processa o upload via AJAX (rota POST /upload/handler)
    public function handle() {
        header('Content-Type: application/json');

        // 1. Verificação de Permissão
        $authorType = 'admin';
        $authorId = $_SESSION['admin_id'] ?? 0;

        if (isset($_SESSION['aluno_id'])) {
            if (!in_array($_SESSION['aluno_role'] ?? '', ['professor', 'special'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permissão negada.']);
                return;
            }
            $authorType = 'user';
            $authorId = $_SESSION['aluno_id'];
        } elseif (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Não autenticado.']);
            return;
        }

        // 2. Validação
        $titulo = $_POST['titulo'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $arquivo_midia = $_FILES['arquivo'] ?? null;
        $arquivo_cover = $_FILES['cover'] ?? null;

        if (!$titulo || !$tipo || !$arquivo_midia || $arquivo_midia['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Dados inválidos ou arquivo corrompido.']);
            return;
        }

        // 3. Upload Mídia
        $fileName = uniqid() . '_' . basename($arquivo_midia['name']);
        $subDir = $tipo; 
        
        // Caminho físico (para mover)
        // $uploadPath = BASE_PATH . "/public/media/$subDir/" . $fileName;
        $uploadPath = "/mnt/etecast/Media/media/$subDir/" . $fileName;
        $dbPath = "$subDir/" . $fileName;

        if (!move_uploaded_file($arquivo_midia['tmp_name'], $uploadPath)) {
            echo json_encode(['success' => false, 'error' => 'Falha ao salvar arquivo (Permissão de pasta).']);
            return;
        }

        // 4. Upload Capa (Opcional)
        $dbPathCover = null;
        if ($arquivo_cover && $arquivo_cover['error'] === UPLOAD_ERR_OK) {
            $coverName = uniqid() . '_' . basename($arquivo_cover['name']);
            $coverPath = BASE_PATH . "/public/media/covers/" . $coverName;
            
            if (!is_dir(dirname($coverPath))) mkdir(dirname($coverPath), 0775, true);

            if (move_uploaded_file($arquivo_cover['tmp_name'], $coverPath)) {
                $dbPathCover = "covers/" . $coverName;
            }
        }

        // 5. Salvar no Banco
        try {
            $content = new Content();
            $content->createAdvanced($tipo, $titulo, $_POST['descricao'] ?? '', $dbPath, $dbPathCover, $authorType, $authorId);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Erro SQL: ' . $e->getMessage()]);
        }
    }
}
