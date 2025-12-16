<?php

// --- 1. Configuração do Banco de Dados ---
define('DB_HOST', 'localhost'); 
define('DB_NAME', 'etecast_db');
define('DB_USER', 'etecast_user');
define('DB_PASS', 'etecast#2025'); 

// --- 2. Configurações da Aplicação ---
//$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
//$domain = $_SERVER['HTTP_HOST'];
//define('SITE_URL', $protocol . '://' . $domain);
if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $domain = $_SERVER['HTTP_X_FORWARDED_HOST'];
} else {
    $domain = $_SERVER['HTTP_HOST'];
}

// Remove porta se existir
$domain = preg_replace('/:\d+$/', '', $domain);

define('SITE_URL', 'http://' . $domain);
//define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']); // REMOTO
//define('SITE_URL', 'http://etecast.local'); // LOCAL
define('DEBUG_MODE', true);

// Timezone (Correto, como você definiu)
date_default_timezone_set('America/Sao_Paulo');

// --- 3. Configurações de Sessão  ---
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
define('SESSION_TTL', 1800); 

// --- 4. Segurança de Mídia (HMAC)  ---

define('HMAC_SECRET_KEY', 'm4Bn#mxrnX4q^ToSgRVX!B$XMZ3oAY');

?>
