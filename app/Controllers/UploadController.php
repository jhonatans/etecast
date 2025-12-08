<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Content;

class UploadController extends Controller {

    public function handle() {
        // Retornar JSON sempre
        header('Content-Type: application/json');

        // Verificar Permissão (Admin, Professor ou Especial)
        if (!isset($_SESSION['admin_id']) && !isset($_SESSION['aluno_id'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Não autenticado']);
            return;
        }

        // Se for aluno, verificar se é Professor ou Especial
        $authorType = 'admin';
        $authorId = $_SESSION['admin_id'] ?? 0;

        if (isset($_SESSION['aluno_id'])) {
            // Verifica na sessão se o papel permite 
            if (!in_array($_SESSION['aluno_role'], ['professor', 'special'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Você não tem permissão para fazer upload.']);
                return;
            }
            $authorType = 'user';
            $authorId = $_SESSION['aluno_id'];
        }

        // Lógica de Upload 
        $titulo = $_POST['titulo'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        
        $arquivo_midia = $_FILES['arquivo'] ?? null;
        $arquivo_cover = $_FILES['cover'] ?? null;

        if (!$titulo || !$tipo || !$arquivo_midia || $arquivo_midia['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Dados inválidos ou arquivo corrompido.']);
            return;
        }

        // Upload Mídia
        $fileName = uniqid() . '_' . basename($arquivo_midia['name']);
        $subDir = $tipo;
        $uploadPath = BASE_PATH . "/public/media/$subDir/" . $fileName;
        $dbPath = "$subDir/" . $fileName;

        if (!move_uploaded_file($arquivo_midia['tmp_name'], $uploadPath)) {
            echo json_encode(['success' => false, 'error' => 'Falha ao salvar arquivo no disco. Verifique permissões.']);
            return;
        }

        // Upload Capa
        $dbPathCover = null;
        if ($arquivo_cover && $arquivo_cover['error'] === UPLOAD_ERR_OK) {
            $coverName = uniqid() . '_' . basename($arquivo_cover['name']);
            $coverPath = BASE_PATH . "/public/media/covers/" . $coverName;
            if (move_uploaded_file($arquivo_cover['tmp_name'], $coverPath)) {
                $dbPathCover = "covers/" . $coverName;
            }
        }

        // Salvar no Banco
        try {
            $content = new Content();
            $content->createAdvanced($tipo, $titulo, $_POST['descricao'] ?? '', $dbPath, $dbPathCover, $authorType, $authorId);
            
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Erro de Banco de Dados: ' . $e->getMessage()]);
        }
    }
}