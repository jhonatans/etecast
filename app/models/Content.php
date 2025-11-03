<?php

namespace app\models;
use PDO;

class Content {
    protected $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM contents WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($tipo, $titulo, $descricao, $arquivo, $hlsManifest, $criadoPor) {
        $stmt = $this->pdo->prepare(
            'INSERT INTO contents (tipo, titulo, descricao, arquivo, hls_manifest, criado_por, visivel) 
             VALUES (?, ?, ?, ?, ?, ?, 1)'
        );
        $stmt->execute([$tipo, $titulo, $descricao, $arquivo, $hlsManifest, $criadoPor]);
        return $this->pdo->lastInsertId();
    }
}