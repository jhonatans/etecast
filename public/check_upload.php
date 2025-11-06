<?php
echo "<h3>Configurações de Upload - ETECast</h3>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";

// Verificar se é suficiente para 800MB
$upload_limit = ini_get('upload_max_filesize');
$post_limit = ini_get('post_max_size');

echo "<h4>Status:</h4>";
if (strpos($upload_limit, '800M') !== false && strpos($post_limit, '800M') !== false) {
    echo "✅ Configuração correta para 800MB";
} else {
    echo "❌ Configuração insuficiente. Atual: $upload_limit, $post_limit";
}