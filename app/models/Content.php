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

    public function create($tipo, $titulo, $descricao, $capaUrl, $arquivo, $hlsManifest, $criadoPor, $status = 'processing') {
        $stmt = $this->pdo->prepare(
            'INSERT INTO contents (tipo, titulo, descricao, capa_url, arquivo, hls_manifest, criado_por, status, visivel) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $stmt->execute([$tipo, $titulo, $descricao, $capaUrl, $arquivo, $hlsManifest, $criadoPor, $status]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Busca todos os conteúdos visíveis, ordenados pelos mais recentes.
     * @return array
     */
    public function findAllVisible() {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM contents WHERE visivel = 1 AND status = 'available' ORDER BY criado_em DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza um conteúdo após o processamento do worker.
     */
    public function updateAsProcessed($contentId, $arquivo, $hlsManifest) {
        $stmt = $this->pdo->prepare(
            "UPDATE contents 
            SET status = 'available', arquivo = ?, hls_manifest = ? 
            WHERE id = ?"
        );
        return $stmt->execute([$arquivo, $hlsManifest, $contentId]);
    }

    /**
     * Marca um conteúdo como 'failed' se a transcodificação falhar.
     */
    public function updateAsFailed($contentId) {
        $stmt = $this->pdo->prepare("UPDATE contents SET status = 'failed' WHERE id = ?");
        return $stmt->execute([$contentId]);
    }

    
}