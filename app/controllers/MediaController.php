<?php
namespace app\controllers;

use app\models\Content;
use app\models\Token;
use PDO;

class MediaController {
    protected $pdo;
    protected $tokenModel;
    protected $contentModel;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->tokenModel = new Token();
        $this->contentModel = new Content($pdo);
    }

    /**
     * Serve um arquivo de mídia protegido via X-Accel-Redirect do Nginx.
     * A URL esperada é /stream/{contentId}/{filePath}?t={token}
     *
     * @param int $contentId ID do conteúdo no banco de dados.
     * @param string $filePath Caminho relativo do arquivo (ex: 'video/123/stream.m3u8' ou 'podcast/audio.mp3').
     * @param string|null $token O token de segurança gerado pela aplicação.
     */
    public function serve($contentId, $filePath, $token = null) {
        // O Middleware global já verificou se o aluno está logado.
        // Aqui, verifica se o token é válido para o recurso solicitado.

        // Sanitização básica do filePath para prevenir Path Traversal
        $sanitizedFilePath = str_replace(['..', './'], '', $filePath);

        // 1. Buscar informações do conteúdo para verificar permissão e caminho real
        $content = $this->contentModel->findById($contentId);
        if (!$content || $content['visivel'] == 0) {
            $this->sendError(404, "Conteúdo não encontrado ou indisponível.");
        }

        // Determinar o caminho real esperado para o token
        $expectedResourcePath = $content['arquivo'];
        if ($content['tipo'] === 'video' && $content['hls_manifest'] && basename($sanitizedFilePath) === basename($content['hls_manifest'])) {
            // Se for um vídeo e estiver pedindo o manifesto HLS, use o caminho do manifesto
            $expectedResourcePath = $content['hls_manifest'];
        } else if ($content['tipo'] === 'video' && strpos($sanitizedFilePath, 'stream_') !== false) {
             // Se for um segmento TS de vídeo, o token é gerado para o manifesto principal,
             // mas o recurso sendo validado é o segmento. É um pouco mais complexo aqui.
             // Simplificação: para HLS, o player hls.js vai pedir vários arquivos .ts.
             // O token gerado será para o manifesto .m3u8, e o Nginx precisará ser configurado
             // para permitir os .ts se o .m3u8 tiver sido validado via X-Accel-Redirect.
             // Para esta fase, vamos exigir que o token seja para o $content['arquivo'] ou $content['hls_manifest']
             // e deixamos o Nginx servir os segmentos.
             $expectedResourcePath = $content['hls_manifest'] ?: $content['arquivo'];

        } else if (basename($sanitizedFilePath) !== basename($content['arquivo'])) {
            // Se o arquivo solicitado não for o principal (e não for um segmento HLS), algo está errado
            $this->sendError(403, "Acesso ao recurso não autorizado ou arquivo inválido.");
        }


        // Construir o caminho completo dentro de media_protected/ para validação do token
        $fullPathInProtected = $content['tipo'] . '/' . $sanitizedFilePath;
        
        // 2. Validar o token para o caminho completo do arquivo
        $validatedPath = $this->tokenModel->validate($token, $fullPathInProtected);

        if ($validatedPath === false) {
            $this->sendError(403, "Token inválido, expirado ou não corresponde ao recurso.");
        }

        // 3. Montar o caminho real no sistema de arquivos
        $realSystemPath = MEDIA_PROTECTED_PATH . $validatedPath;

        if (!file_exists($realSystemPath)) {
            $this->sendError(404, "Arquivo de mídia não encontrado no servidor: " . $realSystemPath);
        }

        // 4. A MÁGICA: X-Accel-Redirect para o Nginx servir o arquivo
        // O Nginx terá uma 'location /protected_media/' que mapeia para MEDIA_PROTECTED_PATH
        $nginxRedirectPath = '/protected_media/' . $validatedPath;

        header_remove(); // Remove todos os cabeçalhos PHP padrão
        header("Content-Disposition: inline"); // Tenta exibir no navegador, não baixar
        header("Cache-Control: private, no-store, no-cache, must-revalidate");
        header("X-Content-Type-Options: nosniff"); // Previne ataques de "sniffing" de MIME-type
        
        // Registrar o acesso no log
        // (new AccessLog($this->pdo))->logAccess($_SESSION['student_id'], $contentId, $content['tipo'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

        // Envia o cabeçalho X-Accel-Redirect
        header("X-Accel-Redirect: " . $nginxRedirectPath);
        exit();
    }

    private function sendError($statusCode, $message) {
        http_response_code($statusCode);
        echo json_encode(['error' => $message]);
        exit();
    }
}