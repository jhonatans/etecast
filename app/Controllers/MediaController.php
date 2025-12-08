<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\MediaHelper;
use App\Models\AccessLog;

class MediaController extends Controller {

    public function getSecureMedia() {
        
        if (!isset($_SESSION['aluno_id'])) {
            $this->forbidden("Acesso não autenticado.");
            return;
        }

        // 2. Coletar os parâmetros 
        $filePath = $_GET['file'] ?? null;
        $contentId = (int)($_GET['id'] ?? 0);
        $expires = (int)($_GET['expires'] ?? 0);
        $token = $_GET['token'] ?? null;

        if (!$filePath || !$contentId || !$expires || !$token) {
            $this->forbidden("Parâmetros inválidos.");
            return;
        }

        // 3. Validar o Token 
        if (!MediaHelper::validateToken($filePath, $contentId, $expires, $token)) {
            $this->forbidden("Token inválido ou expirado.");
            return;
        }

        if (str_contains($filePath, '..')) {
            $this->forbidden("Tentativa de acesso inválida.");
            return;
        }
        
        // 5. LOG DE ACESSO
        try {
            $log = new AccessLog();
            $ip = $_SERVER['REMOTE_ADDR'];
            $agente = $_SERVER['HTTP_USER_AGENT'];
            
            // Mapear o tipo de ação
            $tipo = $this->getTipoAcao($filePath);
            
            $log->log($_SESSION['aluno_id'], $contentId, $tipo, $ip, $agente);
            
        } catch (\Exception $e) {
            error_log("Falha ao registrar log de acesso: " . $e->getMessage());
        }

        // 6. [ENTREGA] X-Accel-Redirect
        $internalPath = '/internal_media/' . $filePath;
        ob_end_clean(); 
        header("X-Accel-Redirect: " . $internalPath);
        exit;
    }

    private function getTipoAcao(string $filePath): string {
        if (str_ends_with($filePath, '.pdf')) return 'ler';
        if (str_ends_with($filePath, '.mp3')) return 'ouvir';
        return 'assistir';
    }

    private function forbidden(string $message) {
        http_response_code(403);
        echo "<h1>403 - Acesso Negado</h1><p>$message</p>";
    }
}