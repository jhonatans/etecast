<?php

namespace App\Helpers;

class MediaHelper {
    
    /**
     * Gera uma URL de mídia segura e temporária.
     *
     * @param string $filePath Caminho relativo do arquivo 
     * @param int $duration Duração do token em segundos 
     * @return string A URL segura 
     */
    public static function getSecureUrl(string $filePath, int $contentId, int $duration = 600): string {
        $expires = time() + $duration;
        
        // Dados que o token irá proteger (agora inclui o ID)
        $dataToSign = $filePath . $contentId . $expires;
        
        $secretKey = HMAC_SECRET_KEY; 
        $token = hash_hmac('sha256', $dataToSign, $secretKey);
        
        // Montar a URL (agora com &id=)
        return sprintf(
            "/secure_media?file=%s&id=%d&expires=%d&token=%s",
            urlencode($filePath),
            $contentId,
            $expires,
            $token
        );
    }

    /**
     * Valida um token de mídia recebido.
     *
     * @param string $filePath
     * @param int $expires
     * @param string $tokenRecebido
     * @return bool
     */
    public static function validateToken(string $filePath, int $contentId, int $expires, string $tokenRecebido): bool {
        // 1. Checar expiração
        if (time() > $expires) {
            return false;
        }
        
        // 2. Recalcular o token esperado (agora inclui o ID)
        $secretKey = HMAC_SECRET_KEY;
        $dataToSign = $filePath . $contentId . $expires;
        $tokenEsperado = hash_hmac('sha256', $dataToSign, $secretKey);
        
        // 3. Comparar
        return hash_equals($tokenEsperado, $tokenRecebido);
    }
}

// Registrar este Helper no Autoloader
// Adicione esta linha em /var/www/etecast/public/index.php (junto com os outros require_once)
// require_once BASE_PATH . '/app/Helpers/MediaHelper.php';