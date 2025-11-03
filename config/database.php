<?php

$config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('DB_DATABASE') ?: 'etecast', 
    'username' => getenv('DB_USERNAME') ?: 'etecast_user', 
    'password' => getenv('DB_PASSWORD') ?: '', 
    'charset' => 'utf8mb4'
];

$dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
} catch (\PDOException $e) {
    error_log('DB connection error: ' . $e->getMessage());
    die('Erro de conexão com o banco de dados. Verifique os logs.');
}