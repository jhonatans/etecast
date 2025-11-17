<?php

namespace App\Core;

class Autoloader {
    
    public static function register() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Este método é chamado automaticamente pelo PHP
     * sempre que uma classe é usada pela primeira vez.
     * @param string
     */
    public static function autoload($className) {
        
        // 1. Define o prefixo do nosso namespace ("App\")
        $prefix = 'App\\';
        $len = strlen($prefix);

        // 2. Define o diretório base para este prefixo
        $base_dir = BASE_PATH . '/app/';

        // 3. Verifica se a classe é do nosso projeto
        if (strncmp($prefix, $className, $len) !== 0) {
            return;
        }

        // 4. Pega o nome relativo da classe
        $relative_class = substr($className, $len);

        // 5. Monta o caminho completo do arquivo
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // 6. Se o arquivo existir, carrega ele
        if (file_exists($file)) {
            require_once $file;
        } else {
            // (Ajuda para depuração: se o arquivo não for encontrado)
            // error_log("Autoloader: Arquivo não encontrado - " . $file);
        }
    }
}