<?php
// Definições de fuso horário
date_default_timezone_set('America/Recife');

// Nível de relatório de erros em ambiente de desenvolvimento (mudar para 0 em produção)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Chave secreta para criptografia.
define('APP_SECRET', getenv('APP_SECRET') ?: 'teste1234');
// define('APP_KEY', getenv('APP_KEY') ?: '');
//var_dump('APP_SECRET definida como: ' . APP_SECRET . "\n");

// Configurações do Redis
define('REDIS_HOST', getenv('REDIS_HOST') ?: '127.0.0.1');
define('REDIS_PORT', getenv('REDIS_PORT') ?: 6379);
define('REDIS_PASS', getenv('REDIS_PASS') ?: null);

// Paths da aplicação
define('STORAGE_UPLOADS_QUEUE_PATH', __DIR__ . '/../storage/uploads_queue/');
define('MEDIA_PROTECTED_PATH', __DIR__ . '/../media_protected/');
define('FFMPEG_LOG_PATH', __DIR__ . '/../storage/logs/ffmpeg.log');
define('APP_LOG_PATH', __DIR__ . '/../storage/logs/app.log');

// URL base da aplicação
define('BASE_URL', getenv('BASE_URL') ?: 'http://etecast.local');

// Configurações de sessão PHP
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 1800); // 30 minutos de inatividade
ini_set('session.cookie_lifetime', 0); // Cookie expira com o navegador