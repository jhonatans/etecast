<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Content;
use App\Helpers\MediaHelper;

class StudentController extends Controller {

    private $contentModel;

    public function __construct() {
        if (!isset($_SESSION['aluno_id'])) {
            $this->redirect('/');
        }
        $this->contentModel = new Content();
    }

    public function dashboard() {
        // 1. Buscar todos os conteúdos visíveis
        $todosConteudos = $this->contentModel->findAllVisible();
        $this->view('student/dashboard', [
            'titulo' => 'Meu Dashboard',
            'conteudos' => $todosConteudos
        ]);

        // 2. Buscar Top 5
        $top5 = $this->contentModel->getTop5();

        // 3. Separar por categorias
        $videos = array_filter($todosConteudos, fn($c) => $c['tipo'] === 'video');
        $podcasts = array_filter($todosConteudos, fn($c) => $c['tipo'] === 'podcast');
        $pdfs = array_filter($todosConteudos, fn($c) => $c['tipo'] === 'pdf');

        // 4. Enviar tudo para a View
        $this->view('student/dashboard', [
            'titulo' => 'Meu Dashboard',
            'top5' => $top5,
            'videos' => $videos,
            'podcasts' => $podcasts,
            'pdfs' => $pdfs
        ]);
    }

    public function showContent($id) {
        $conteudo = $this->contentModel->findById($id);

        if (!$conteudo) {
            http_response_code(404);
            echo "Conteúdo não encontrado.";
            return;
        }

        $secureUrl = MediaHelper::getSecureUrl($conteudo['arquivo'], $conteudo['id'], 600);
        
        $this->view('student/player', [
            'titulo' => $conteudo['titulo'],
            'conteudo' => $conteudo,
            'secureUrl' => $secureUrl
        ]);
    }
}