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
        $colors = [ 'primary-blue' => '#4285F4' ];
        
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
        // CORREÇÃO: Usar TIPO/ID/FILENAME
        $filename = basename($content['hls_manifest'] ?: $content['arquivo']);
        $resourcePath = $content['tipo'] . '/' . $contentId . '/' . $filename; 
        
        // ADICIONADO: Garantia de que o recurso existe
        if (empty($filename)) { // Verifica se $filename é nulo/vazio
            echo "Erro: Conteúdo mal configurado (sem 'hls_manifest' ou 'arquivo').";
            return;
        }

        // 3. Gera o token para o recurso
        $token = $tokenModel->generate($resourcePath, 600); // 10 minutos

        // 4. Constrói a URL BASE (sem token)
        $manifestBaseUrl = BASE_URL . '/stream/' . $contentId . '/' . basename($resourcePath);
        
        // 5. Constrói a URL segura COMPLETA (para fallback e outros tipos)
        $streamingUrl = $manifestBaseUrl . '?t=' . $token;

        // 6. Define as cores e o título da página
        $pageTitle = htmlspecialchars($content['titulo']);
        $colors = [ 'primary-blue' => '#4285F4' ];



        // var_dump("TESTE DO CONTROLLER:", $manifestBaseUrl, $token);
        // die();
        // 7. Carrega a view do player
        require __DIR__ . '/../views/student/player.php';
    }
}