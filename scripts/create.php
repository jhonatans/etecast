<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/Core/Database.php';

echo "🎯 Criador de Usuários - ETECast\n";
echo "1. Administrador\n";
echo "2. Aluno\n";

$opcao = readline("Escolha uma opção (1 ou 2): ");

try {
    $db = App\Core\Database::getInstance();

    if ($opcao == '1') {
        $username = readline("Username: ");
        $password = readline("Password: ");
        
        $hash = password_hash($password, PASSWORD_ARGON2ID);
        $stmt = $db->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
        $stmt->execute([$username, $hash]);
        
        echo "✅ Administrador criado com sucesso!\n";
        
    } elseif ($opcao == '2') {
        $matricula = readline("Matrícula: ");
        $nome = readline("Nome: ");
        $data_nascimento = readline("Data Nascimento (YYYY-MM-DD): ");
        
        $stmt = $db->prepare("INSERT INTO students (matricula, nome, data_nascimento) VALUES (?, ?, ?)");
        $stmt->execute([$matricula, $nome, $data_nascimento]);
        
        echo "✅ Aluno criado com sucesso!\n";
        
    } else {
        echo "❌ Opção inválida!\n";
    }

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}