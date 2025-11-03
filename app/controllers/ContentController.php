<?php
namespace app\controllers;

use app\models\Content;
use app\models\Admin; 
use PDO;

class ContentController {
    protected $pdo;
    protected $contentModel;
    protected $adminModel; 

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->contentModel = new Content($pdo);
        $this->adminModel = new Admin($pdo);
    }

    public function showUploadForm() {
        $error = $_SESSION['upload_error'] ?? null;
        $success = $_SESSION['upload_success'] ?? null;
        unset($_SESSION['upload_error'], $_SESSION['upload_success']);
        
        $colors = [
            'primary-blue' => '#4285F4', // Azul do fundo da logo
            'ete-yellow' => '#FFC107',  // Amarelo do E
            'ete-green' => '#4CAF50',   // Verde do T
            'ete-red' => '#F44336',     // Vermelho do E
            'white' => '#FFFFFF',       // Branco
        ];

        require __DIR__ . '/../views/admin/upload_form.php';
    }

    public function uploadContent($postData, $fileData) {
        // 1. Validação básica do Admin logado
        if (!isset($_SESSION['admin_id'])) {
            $_SESSION['upload_error'] = 'Você não tem permissão para realizar esta ação.';
            header('Location: ' . BASE_URL . '/admin/content/upload');
            exit();
        }

        // 2. Validação dos dados do formulário
        $title = trim($postData['title'] ?? '');
        $description = trim($postData['description'] ?? '');
        $contentType = trim($postData['content_type'] ?? '');

        if (empty($title) || empty($contentType) || !in_array($contentType, ['video', 'podcast', 'pdf'])) {
            $_SESSION['upload_error'] = 'Preencha todos os campos obrigatórios e selecione um tipo válido.';
            header('Location: ' . BASE_URL . '/admin/content/upload');
            exit();
        }

        // 3. Validação do arquivo enviado
        if (!isset($fileData['content_file']) || $fileData['content_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['upload_error'] = 'Erro ao enviar o arquivo. Código: ' . $fileData['content_file']['error'];
            header('Location: ' . BASE_URL . '/admin/content/upload');
            exit();
        }

        $tempFilePath = $fileData['content_file']['tmp_name'];
        $originalFileName = basename($fileData['content_file']['name']);
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

        // Validar extensões de arquivo
        $allowedExtensions = [
            'video' => ['mp4', 'mov', 'avi', 'mkv', 'webm'],
            'podcast' => ['mp3', 'wav', 'ogg'],
            'pdf' => ['pdf']
        ];

        if (!in_array($fileExtension, $allowedExtensions[$contentType] ?? [])) {
            $_SESSION['upload_error'] = 'Tipo de arquivo não permitido para ' . $contentType . '. Extensões válidas: ' . implode(', ', $allowedExtensions[$contentType]);
            header('Location: ' . BASE_URL . '/admin/content/upload');
            exit();
        }

        // 4. Mover o arquivo para a fila de uploads temporários
        $uniqueFileName = uniqid('upload_') . '_' . $originalFileName;
        $destinationPath = STORAGE_UPLOADS_QUEUE_PATH . $uniqueFileName;

        if (!move_uploaded_file($tempFilePath, $destinationPath)) {
            $_SESSION['upload_error'] = 'Falha ao mover o arquivo enviado para a fila de processamento.';
            error_log("ContentController: Falha ao mover arquivo {$tempFilePath} para {$destinationPath}");
            header('Location: ' . BASE_URL . '/admin/content/upload');
            exit();
        }

        // 5. Registrar o trabalho na fila de processamento (tabela `queue_jobs`)
        try {
            $stmt = $this->pdo->prepare("INSERT INTO queue_jobs (status, job_type, original_file_path, title, description, content_type, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                'pending',
                'transcode_media', 
                $destinationPath,
                $title,
                $description,
                $contentType,
                $_SESSION['admin_id']
            ]);
            $jobId = $this->pdo->lastInsertId();

            $_SESSION['upload_success'] = 'Conteúdo "' . htmlspecialchars($title) . '" enviado com sucesso. O processamento (transcodificação) iniciará em breve. Job ID: ' . $jobId;
            header('Location: ' . BASE_URL . '/admin/content/upload');
            exit();

        } catch (\PDOException $e) {
            $_SESSION['upload_error'] = 'Erro interno ao registrar o trabalho: ' . $e->getMessage();
            error_log("ContentController: Erro ao inserir job na fila: " . $e->getMessage());
            // Tentar remover o arquivo do storage/uploads_queue se o DB falhar
            unlink($destinationPath);
            header('Location: ' . BASE_URL . '/admin/content/upload');
            exit();
        }
    }
    
    /**
     * Retorna o status de um trabalho de upload/transcodificação.
     * @param int $jobId
     */
    public function getUploadStatus($jobId) {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado.']);
            exit();
        }

        try {
            $stmt = $this->pdo->prepare("SELECT status, log, created_at, finished_at FROM queue_jobs WHERE id = ?");
            $stmt->execute([$jobId]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($job) {
                echo json_encode($job);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Job ID não encontrado.']);
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao buscar status do job: ' . $e->getMessage()]);
            error_log("ContentController: Erro ao buscar job status: " . $e->getMessage());
        }
        exit();
    }
}

// // Model para Content
// namespace App\Models;
// use PDO;

// class Content {
//     protected $pdo;
//     public function __construct(PDO $pdo) { $this->pdo = $pdo; }
//     public function findById($id) {
//         $stmt = $this->pdo->prepare('SELECT * FROM contents WHERE id = ? LIMIT 1');
//         $stmt->execute([$id]);
//         return $stmt->fetch(PDO::FETCH_ASSOC);
//     }
    
// }