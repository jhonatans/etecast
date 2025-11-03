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

    // Exibe o catálogo principal
    public function catalog() {
        // (Middleware já protegeu esta rota)
        $studentName = $_SESSION['student_nome'] ?? 'Aluno';
        echo "<h1>Catálogo ETECast</h1>";
        echo "Bem-vindo, " . htmlspecialchars($studentName);
        echo '<br><a href="/logout">Sair</a>';
        
        // (Lógica para buscar conteúdos do $this->contentModel e exibir)
    }

    // Exibe o player para um conteúdo
    public function viewContent($contentId) {
        // (Middleware já protegeu esta rota)
        $content = $this->contentModel->findById($contentId);
        if (!$content) {
            echo "Conteúdo não encontrado.";
            return;
        }

        // Gerar um token de 10 minutos para este arquivo
        $tokenModel = new Token();
        $resourcePath = $content['hls_manifest'] ?: $content['arquivo'];
        $token = $tokenModel->generate($resourcePath, 600);
        $streamingUrl = BASE_URL . '/stream/' . $contentId . '/' . $resourcePath . '?t=' . $token;

        echo "<h1>Player: " . htmlspecialchars($content['titulo']) . "</h1>";
        echo "<p>" . htmlspecialchars($content['descricao']) . "</p>";

        // (Aqui entraria a lógica do player HLS.js ou PDF.js)
        echo "URL do Stream (DEBUG): " . $streamingUrl;
    }
}