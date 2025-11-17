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
        $conteudos = $this->contentModel->findAllVisible();
        $this->view('student/dashboard', [
            'titulo' => 'Meu Dashboard',
            'conteudos' => $conteudos
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