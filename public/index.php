<?php
opcache_reset();
// 1. Carrega o Autoloader do Composer (ESSENCIAL)
require __DIR__ . '/../vendor/autoload.php';
// use Dotenv\Dotenv;

// $envFile = __DIR__ . '/../.env';
// if (file_exists($envFile)) {
//     $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
//     foreach ($lines as $line) {
//         if (strpos(trim($line), '#') === 0) continue;
        
//         list($key, $value) = explode('=', $line, 2);
//         $key = trim($key);
//         $value = trim($value);
        
//         // Remove aspas se existirem
//         $value = trim($value, '"\'');
        
//         putenv("$key=$value");
//         $_ENV[$key] = $value;
//         $_SERVER[$key] = $value;
//     }
// }
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..'); // Aponta para a raiz /etecast
$dotenv->load();

// 2. Carrega as Configurações Globais (Constantes)
require __DIR__ . '/../config/app.php';

// 3. Carrega e cria a Conexão com o Banco de Dados (Variável $pdo)
require __DIR__ . '/../config/database.php';

session_start();

// Importa as classes
use Bramus\Router\Router;
use app\controllers\AuthController;
use app\controllers\AdminAuthController;
use app\controllers\AdminController;
use app\controllers\ContentController;
use app\controllers\MediaController;
use app\controllers\StudentController;

$router = new Router();

// --------------------------------------------------------------------------
// Middlewares Corrigidos - ORDEM IMPORTANTE!
// --------------------------------------------------------------------------

// PRIMEIRO: Middleware para ADMIN (mais específico)
$router->before('GET|POST', '/admin(/.*)?', function() {
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Permite acesso à página de login do admin
    if (strpos($currentPath, '/admin/login') !== false) {
        return;
    }
    
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/admin/login');
        exit();
    }
});

// SEGUNDO: Middleware para ALUNO (menos específico)
$router->before('GET|POST', '/|/(dashboard|catalog|content|history|logout)', function() {
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = str_replace(BASE_URL, '', $currentPath);
    
    // Se for uma rota de admin, ignora este middleware
    if (strpos($currentPath, '/admin') === 0) {
        return;
    }
    
    // Permite acesso às rotas de login e registro de senha
    if ($basePath === '/login' || $basePath === '/register-password') {
        return;
    }
    
    if (!isset($_SESSION['student_id'])) {
        header('Location: ' . BASE_URL . '/login');
        exit();
    }
});

// --------------------------------------------------------------------------
// Rotas de Autenticação de Aluno (Injetando $pdo)
// --------------------------------------------------------------------------
$authController = new AuthController($pdo);
$router->get('/login', function() use ($authController) { 
    // Se já estiver logado, redireciona para a página inicial
    if (isset($_SESSION['student_id'])) {
        header('Location: ' . BASE_URL . '/');
        exit();
    }
    $authController->showLogin(); 
});
$router->post('/login', function() use ($authController) { $authController->login($_POST); });
$router->get('/register-password', function() use ($authController) { $authController->showRegisterPassword(); });
$router->post('/register-password', function() use ($authController) { $authController->registerPassword($_POST); });
$router->get('/logout', function() use ($authController) { $authController->logout(); });

// --------------------------------------------------------------------------
// Rotas do Painel Administrativo (Injetando $pdo)
// --------------------------------------------------------------------------
$adminAuthController = new AdminAuthController($pdo);
$adminController = new AdminController($pdo);
$contentController = new ContentController($pdo);

$router->get('/admin/login', function() use ($adminAuthController) { 
    // Se já estiver logado como admin, redireciona para o dashboard
    if (isset($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/admin');
        exit();
    }
    $adminAuthController->showLogin(); 
});
$router->post('/admin/login', function() use ($adminAuthController) { $adminAuthController->login($_POST); });
$router->get('/admin/logout', function() use ($adminAuthController) { $adminAuthController->logout(); });
$router->get('/admin', function() use ($adminController) { $adminController->dashboard(); });

// (Adicione suas outras rotas de admin aqui)
$router->get('/admin/content/upload', function() use ($contentController) { $contentController->showUploadForm(); });
$router->post('/admin/content/upload', function() use ($contentController) { $contentController->uploadContent($_POST, $_FILES); });
$router->get('/admin/content/upload-status/{job_id}', function($job_id) use ($contentController) { $contentController->getUploadStatus($job_id); });

// --------------------------------------------------------------------------
// Rotas de Conteúdo e Streaming de Aluno (Injetando $pdo)
// --------------------------------------------------------------------------
$mediaController = new MediaController($pdo);
$studentController = new StudentController($pdo);

// Rota principal (Home/Catálogo)
$router->get('/', function() use ($studentController) {
    $studentController->catalog();
});

// Rota para exibir o player (vídeo, podcast, PDF)
$router->get('/content/view/(\d+)', function($contentId) use ($studentController) {
    $studentController->viewContent($contentId);
});

// Rota de streaming de mídia (protegida por token)
$router->get('/stream/(\d+)/(.+)', function($contentId, $filePath) use ($mediaController) {
    $mediaController->serve($contentId, $filePath, $_GET['t'] ?? null);
});

// Rota do catálogo (alternativa)
$router->get('/catalog', function() use ($studentController) {
    $studentController->catalog();
});

// Rota do histórico
$router->get('/history', function() use ($studentController) {
    $studentController->history();
});

// Rota do dashboard do aluno
$router->get('/dashboard', function() use ($studentController) {
    $studentController->dashboard();
});

// --------------------------------------------------------------------------
// Tratamento de Erros (404)
// --------------------------------------------------------------------------
$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo '<h1>404 - Página Não Encontrada</h1>';
    exit();
});

// Executa o roteador
$router->run();