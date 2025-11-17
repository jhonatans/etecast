<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);  
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';

// 1. Iniciar a sessão com segurança

session_set_cookie_params([
    'lifetime' => SESSION_TTL,
    'path' => '/',
    'domain' => 'etecast.local', 
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// 2. Carregar Configurações e Autoloader
require_once BASE_PATH . '/app/Core/Autoloader.php';
require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Core/Router.php';

// 3. Registrar o Autoloader
App\Core\Autoloader::register();

// 4. Instanciar e executar o Roteador
$router = new App\Core\Router();

// 5. Definir as rotas da aplicação

// Rotas de Autenticação e Aluno
$router->add('GET', '/', ['AuthController', 'showLogin']);
$router->add('POST', '/login', ['AuthController', 'doLogin']);
$router->add('GET', '/logout', ['AuthController', 'logout']);
$router->add('GET', '/register', ['AuthController', 'showRegister']); // Primeiro acesso
$router->add('POST', '/register', ['AuthController', 'doRegister']); // Primeiro acesso

// Rotas de Autenticação e Aluno
$router->add('GET', '/', ['AuthController', 'showLogin']);
$router->add('POST', '/login', ['AuthController', 'doLogin']);
$router->add('GET', '/logout', ['AuthController', 'logout']);

// Fluxo de Primeiro Acesso
$router->add('GET', '/register', ['AuthController', 'showRegister']); // Pede matrícula + data nasc.
$router->add('POST', '/register', ['AuthController', 'doRegisterCheck']); // Verifica os dados
$router->add('GET', '/create-password', ['AuthController', 'showCreatePassword']); // Mostra form de senha
$router->add('POST', '/create-password', ['AuthController', 'doCreatePassword']); // Salva a senha

// Rotas de Conteúdo (Área do Aluno)
$router->add('GET', '/dashboard', ['StudentController', 'dashboard']);
$router->add('GET', '/content/{id}', ['StudentController', 'showContent']);

// Rota de Mídia Segura
$router->add('GET', '/secure_media', ['MediaController', 'getSecureMedia']);

// Rotas de Administração
$router->add('GET', '/admin/login', ['AdminController', 'showLogin']);
$router->add('POST', '/admin/login', ['AdminController', 'doLogin']);
$router->add('GET', '/admin/logout', ['AdminController', 'logout']);
$router->add('GET', '/admin', ['AdminController', 'dashboard']);
$router->add('GET', '/admin/dashboard', ['AdminController', 'dashboard']);

// Conteúdo
$router->add('GET', '/admin/content', ['AdminController', 'listContent']);
$router->add('GET', '/admin/content/add', ['AdminController', 'showAddContent']);
$router->add('POST', '/admin/content/add', ['AdminController', 'doAddContent']);

// Logs
$router->add('GET', '/admin/logs', ['AdminController', 'listLogs']);


// 6. Despachar a rota
$router->dispatch();