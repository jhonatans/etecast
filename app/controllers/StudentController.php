<?php
namespace app\controllers;

use app\models\Content;
use app\models\Token;
use PDO;

class StudentController {
    protected $pdo;
    protected $contentModel;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->contentModel = new Content($pdo);
    }

    /**
     * Exibe o catálogo principal
     */
    public function catalog() {
        $studentName = $_SESSION['student_nome'] ?? 'Aluno';
        
        // 1. Buscar todos os conteúdos visíveis
        $allContents = $this->contentModel->findAllVisible();

        // 2. Segmentar os conteúdos por tipo
        $videos = [];
        $podcasts = [];
        $livros = [];

        foreach ($allContents as $content) {
            if ($content['tipo'] === 'video') {
                $videos[] = $content;
            } elseif ($content['tipo'] === 'podcast') {
                $podcasts[] = $content;
            } elseif ($content['tipo'] === 'pdf') {
                $livros[] = $content;
            }
        }
        
        $pageTitle = 'Catálogo de Conteúdo';
        $colors = [ 'primary-blue' => '#4285F4' ]; // (Passa cores para o layout)
        
        // 3. Carrega a view do catálogo
        require __DIR__ . '/../views/student/catalog.php';
    }
    /**
     * Exibe o player para um conteúdo
     */
    public function viewContent($contentId) {
        // (O Middleware já protegeu esta rota)
        $studentName = $_SESSION['student_nome'] ?? 'Aluno';
        $content = $this->contentModel->findById($contentId);

        if (!$content || $content['visivel'] == 0) {
            echo "Conteúdo não encontrado."; // Idealmente, uma view de erro
            return;
        }

        // 1. Gerar um token de 10 minutos para este arquivo
        $tokenModel = new Token();

        // 2. Determina o caminho do recurso a ser protegido pelo token
        $resourcePath = $content['hls_manifest'] ?: $content['arquivo'];

        $token = $tokenModel->generate($resourcePath, 600); // 10 minutos

        // 3. Constrói a URL segura que o player usará
        $streamingUrl = BASE_URL . '/stream/' . $contentId . '/' . basename($resourcePath) . '?t=' . $token;

        // 4. Define as cores e o título da página
        $pageTitle = htmlspecialchars($content['titulo']);
        $colors = [ 'primary-blue' => '#4285F4' ];

        // 5. Carrega a view do player
        require __DIR__ . '/../views/student/player.php';
    }
}