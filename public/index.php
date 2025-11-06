<?php
// public/index.php (CORRIGIDO)

// 1. Carrega o Autoloader do Composer (ESSENCIAL)
require __DIR__ . '/../vendor/autoload.php';

// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..'); // Aponta para a raiz /etecast
// $dotenv->load();

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Ignora comentários
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove aspas se existirem
        $value = trim($value, '"\'');
        
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

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
use app\controllers\StudentController; // (Vamos criar este)

$router = new Router();

// --------------------------------------------------------------------------
// Middlewares Globais (O seu código original está correto)
// --------------------------------------------------------------------------
$router->before('GET|POST', '/(dashboard|catalog|content|history|logout)', function() {
    if (!isset($_SESSION['student_id'])) {
        // ... (lógica de exceção para primeiro acesso)
        header('Location: ' . BASE_URL . '/login');
        exit();
    }
});
$router->before('GET|POST', '/admin(/.*)?', function() {
    if (strpos($_SERVER['REQUEST_URI'], '/admin/login') === false) {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit();
        }
    }
});

// --------------------------------------------------------------------------
// Rotas de Autenticação de Aluno (Injetando $pdo)
// --------------------------------------------------------------------------
$authController = new AuthController($pdo); // <-- Injeta o $pdo
$router->get('/login', function() use ($authController) { $authController->showLogin(); });
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

$router->get('/admin/login', function() use ($adminAuthController) { $adminAuthController->showLogin(); });
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
// (Precisamos criar o StudentController)
// $studentController = new StudentController($pdo); 

// Rota principal (Home/Catálogo)
$router->get('/', function() {
    if (!isset($_SESSION['student_id'])) {
        header('Location: ' . BASE_URL . '/login'); exit;
    }
    echo '<h1>Bem-vindo ao ETECast! (Catálogo)</h1> <a href="/logout">Sair</a>';
});

// Rota para exibir o player (vídeo, podcast, PDF)
$router->get('/content/view/(\d+)', function($contentId) {
    // $studentController->viewContent($contentId);
    echo "Exibindo conteúdo $contentId"; // Temporário
});

// Rota de streaming de mídia (protegida por token)
$router->get('/stream/(\d+)/(.+)', function($contentId, $filePath) use ($mediaController) {
    $mediaController->serve($contentId, $filePath, $_GET['t'] ?? null);
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