<?php

namespace App\Core;

class Controller {
    protected function view($viewName, $data = []) {
        extract($data); 
        
        $viewFile = BASE_PATH . "/app/Views/$viewName.php";
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Erro: View '$viewName' não encontrada.");
        }
    }

    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
}