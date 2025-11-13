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
     * A URL esperada é /stream/{contentId}/{fileName}?t={token}
     */
    public function serve($contentId, $fileName, $token = null) {
            
        // $fileName = urldecode($fileName);
        // // 1. VERIFICAÇÃO DO TOKEN
        // if (!$token) {
        //     $this->sendError(403, "Token de acesso necessário.");
        // }

        // // 2. BUSCAR O CONTEÚDO
        // $content = $this->contentModel->findById($contentId);
        // if (!$content) {
        //     $this->sendError(404, "Conteúdo não encontrado.");
        // }

        // // 3. DETERMINAR O RECURSO QUE O TOKEN DEVE PROTEGER
        // // CORREÇÃO CRÍTICA: Deve incluir TIPO/ID/FILENAME, IGUAL AO GERADOR
        // $filename = basename($content['hls_manifest'] ?: $content['arquivo']);
        // $expectedResourcePath = $content['tipo'] . '/' . $contentId . '/' . $filename;

        // if (empty($expectedResourcePath)) {
        //      $this->sendError(500, "Conteúdo mal configurado (sem manifesto ou arquivo).");
        // }

        // // 4. VALIDAR O TOKEN
        // // Verificamos se o token é válido PARA O RECURSO ESPERADO.
        // // if ($this->tokenModel->validate($expectedResourcePath, $token) === false) {
        // //      // Se falhar, envie o erro e pare
        // //      $this->sendError(403, "Token inválido, expirado ou não corresponde ao recurso.");
        // // }

        // //teste
        // http_response_code(209);
        // echo "TOKEN_OK_TEST_209";
        // exit();
        
        // 5. SE O TOKEN É VÁLIDO, O RESTO DO CÓDIGO EXECUTA
        $sanitizedFileName = basename($fileName); 
        
        // Caminho no servidor (usando a constante correta MEDIA_PROTECTED_PATH)
        $realSystemPath = MEDIA_PROTECTED_PATH . $content['tipo'] . '/' . $contentId . '/' . $sanitizedFileName;

        if (!file_exists($realSystemPath)) {
            $this->sendError(404, "Arquivo de mídia não encontrado no servidor: " . $sanitizedFileName);
        }
        
        // 6. X-Accel-Redirect
        // $nginxRedirectPath = '/media_protected/' . $content['tipo'] . '/' . $contentId . '/' . $sanitizedFileName;
        
        // header_remove(); 
        // header("Cache-Control: private");
        
        // if (pathinfo($sanitizedFileName, PATHINFO_EXTENSION) === 'm3u8') {
        //     header("Content-Type: application/vnd.apple.mpegurl");
        // } elseif (pathinfo($sanitizedFileName, PATHINFO_EXTENSION) === 'ts') {
        //     header("Content-Type: video/mp2t");
        // } elseif (pathinfo($sanitizedFileName, PATHINFO_EXTENSION) === 'pdf') {
        //     header("Content-Type: application/pdf");
        // }

        // header("X-Accel-Redirect: " . $nginxRedirectPath);
        // exit();

        // 6. X-Accel-Redirect (DESATIVADO PARA TESTE)
        // $nginxRedirectPath = '/protected_media/' . $content['tipo'] . '/' . $contentId . '/' . $sanitizedFileName;

        header_remove(); 
        header("Cache-Control: private");
        
        // Define o tipo de conteúdo (MIME Type)
        $mime = mime_content_type($realSystemPath); // Usa a função nativa para detetar o tipo

        if (pathinfo($sanitizedFileName, PATHINFO_EXTENSION) === 'm3u8') {
            header("Content-Type: application/vnd.apple.mpegurl");
        } elseif (pathinfo($sanitizedFileName, PATHINFO_EXTENSION) === 'ts') {
            header("Content-Type: video/mp2t");
        } elseif (pathinfo($sanitizedFileName, PATHINFO_EXTENSION) === 'pdf') {
            header("Content-Type: application/pdf");
        } elseif ($mime) {
            header("Content-Type: " . $mime); // Para outros tipos (fallback)
        }
        
        header("Content-Length: " . filesize($realSystemPath));

        // Use readfile() para servir o ficheiro diretamente e ignorar o X-Accel-Redirect
        readfile($realSystemPath);

        // header("X-Accel-Redirect: " . $nginxRedirectPath); // LINHA ANTIGA COMENTADA
        exit();
    }

    private function sendError($statusCode, $message) {
        http_response_code($statusCode);
        echo json_encode(['error' => $message]);
        error_log("MediaController Error: " . $message); // Log de erro do PHP
        exit();
    }
}