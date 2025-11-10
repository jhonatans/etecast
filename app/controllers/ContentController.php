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
        // 1. Validação do Admin
        if (!isset($_SESSION['admin_id'])) {
            $this->redirectWithError('Você não tem permissão.');
        }

        // 2. Validação dos dados do formulário
        $title = trim($postData['title'] ?? '');
        $description = trim($postData['description'] ?? '');
        $contentType = trim($postData['content_type'] ?? '');
        $mediaFile = $fileData['content_file'] ?? null;
        $coverFile = $fileData['cover_image'] ?? null;

        if (empty($title) || empty($contentType) || !$mediaFile || $mediaFile['error'] !== UPLOAD_ERR_OK) {
            $this->redirectWithError('Título, Tipo e Arquivo de Mídia são obrigatórios.');
        }
        
        // 3. Validação e Upload da IMAGEM DE CAPA (se enviada)
        $capaUrl = null;
        if ($coverFile && $coverFile['error'] === UPLOAD_ERR_OK) {
            $coverExt = strtolower(pathinfo($coverFile['name'], PATHINFO_EXTENSION));
            if (!in_array($coverExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                $this->redirectWithError('Formato de capa inválido. Use JPG ou PNG.');
            }
            
            $coverName = uniqid('cover_') . '.' . $coverExt;
            $coverPath = __DIR__ . '/../../public/assets/covers/' . $coverName;
            
            if (move_uploaded_file($coverFile['tmp_name'], $coverPath)) {
                $capaUrl = '/assets/covers/' . $coverName; // Caminho público
            } else {
                $this->redirectWithError('Falha ao mover a imagem de capa.');
            }
        }

        // 4. Validação do ARQUIVO DE MÍDIA
        $fileExtension = strtolower(pathinfo($mediaFile['name'], PATHINFO_EXTENSION));
        $allowedExtensions = [
            'video' => ['mp4', 'mov', 'avi', 'mkv', 'webm'],
            'podcast' => ['mp3', 'wav', 'ogg'],
            'pdf' => ['pdf']
        ];

        if (!in_array($fileExtension, $allowedExtensions[$contentType] ?? [])) {
            $this->redirectWithError('Tipo de arquivo de mídia não permitido.');
        }

        // 5. Mover a MÍDIA para a fila temporária
        $tempMediaFileName = uniqid('upload_') . '_' . basename($mediaFile['name']);
        $tempMediaDestPath = STORAGE_UPLOADS_QUEUE_PATH . $tempMediaFileName;

        if (!move_uploaded_file($mediaFile['tmp_name'], $tempMediaDestPath)) {
            $this->redirectWithError('Falha ao mover o arquivo de mídia para a fila.');
        }

        // 6. INSERIR no banco de dados PRIMEIRO (com status 'processing')
        try {
            // Usando o Model para criar a entrada
            $contentId = $this->contentModel->create(
                $contentType,
                $title,
                $description,
                $capaUrl, // O novo campo
                null, // arquivo (ainda não processado)
                null, // hls_manifest (ainda não processado)
                $_SESSION['admin_id'],
                'processing' // O novo campo
            );
            
            // 7. Registrar o JOB na fila, agora com o content_id
            $stmt = $this->pdo->prepare(
                "INSERT INTO queue_jobs (status, job_type, original_file_path, content_type, admin_id, content_id) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                'pending',
                'transcode_media', 
                $tempMediaDestPath, // O arquivo original
                $contentType,
                $_SESSION['admin_id'],
                $contentId // O ID do conteúdo que acabamos de criar
            ]);

            $_SESSION['upload_success'] = 'Conteúdo "' . htmlspecialchars($title) . '" enviado. O processamento iniciará em breve.';
            header('Location: ' . BASE_URL . '/admin/content/upload');
            exit();

        } catch (\PDOException $e) {
            error_log("Erro no Upload: " . $e->getMessage());
            // Tentar remover os arquivos se o DB falhar
            if (isset($coverPath) && file_exists($coverPath)) unlink($coverPath);
            if (file_exists($tempMediaDestPath)) unlink($tempMediaDestPath);
            $this->redirectWithError('Erro interno de banco de dados.');
        }
    }
    
    // (Função de ajuda para evitar repetição)
    private function redirectWithError($message) {
        $_SESSION['upload_error'] = $message;
        header('Location: ' . BASE_URL . '/admin/content/upload');
        exit();
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


    public function play($id)
    {
        // 1. Buscar o conteúdo pelo ID
        $content = $this->contentModel->findById($id);
        if (!$content) {
            http_response_code(404);
            echo "Conteúdo não encontrado.";
            exit();
        }

        // 2. Montar a URL do streaming de acordo com o tipo
        if ($content['tipo'] === 'video') {
            $streamingUrl = BASE_URL . '/media_protected/video/' . $content['id'] . '/stream.m3u8';
        } elseif ($content['tipo'] === 'podcast') {
            $streamingUrl = BASE_URL . '/media_protected/podcast/' . $content['arquivo'];
        } elseif ($content['tipo'] === 'pdf') {
            $streamingUrl = BASE_URL . '/media_protected/livros/' . $content['arquivo'];
        } else {
            http_response_code(400);
            echo "Tipo de conteúdo inválido.";
            exit();
        }

        // 3. Chamar o player (view existente)
        require __DIR__ . '/../views/content/player.php';
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