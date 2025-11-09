<?php
namespace app\models;

class Token {
    private $secret;

    public function __construct() {
        // A chave secreta definida em config/app.php
        $this->secret = APP_SECRET;
        if (empty($this->secret)) {
            throw new \Exception("APP_SECRET não definida.");
        }
    }

    /**
     * Gera um token de acesso temporário para um recurso.
     * @param string $resourcePath Caminho relativo do arquivo (ex: 'video/123/stream.m3u8')
     * @param int $ttl Tempo de vida do token em segundos (padrão: 10 minutos)
     * @return string Token codificado em base64
     */
    public function generate($resourcePath, $ttl = 600) {
        $exp = time() + $ttl;
        $payload = $resourcePath . '|' . $exp;
        $signature = hash_hmac('sha256', $payload, $this->secret);
        return base64_encode($payload . '|' . $signature);
    }

    /**
     * Valida um token de acesso.
     * @param string $token Token recebido
     * @param string $expectedResourcePath O caminho que o token deveria proteger
     * @return string|false Retorna o caminho do recurso se válido, ou false se inválido/expirado
     */
    public function validate($token, $expectedResourcePath) {
        $decoded = base64_decode($token);
        if (!$decoded) {
            error_log("Token::validate - Falha ao decodificar token.");
            return false;
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            error_log("Token::validate - Formato de token inválido.");
            return false;
        }

        list($resourcePath, $exp, $signature) = $parts;

        // 1. Verificar expiração
        if ($exp < time()) {
            error_log("Token::validate - Token expirado.");
            return false;
        }

        // 2. Verificar se o token é para o recurso esperado
        if ($resourcePath !== $expectedResourcePath) {
            error_log("Token::validate - Recurso no token ('{$resourcePath}') não corresponde ao esperado ('{$expectedResourcePath}').");
            return false;
        }

        // 3. Verificar assinatura
        if (hash_hmac('sha256', $resourcePath . '|' . $exp, $this->secret) !== $signature) {
            error_log("Token::validate - Assinatura do token inválida.");
            return false;
        }

        return $resourcePath;
    }
}