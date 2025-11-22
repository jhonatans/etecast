<?php

namespace App\Models;

class Content extends BaseModel {

    /**
     * Busca um conteúdo pelo ID
     */
    public function findById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM contents WHERE id = ? AND visivel = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Lista todos os conteúdos visíveis
     */
    public function findAllVisible() {
        $stmt = $this->db->query("SELECT id, tipo, titulo, descricao, cover_image, arquivo
                                FROM contents 
                                WHERE visivel = 1 
                                ORDER BY criado_em DESC");
        return $stmt->fetchAll();
    
    }

    /**
     * Busca os 5 conteúdos mais acessados
     */
    public function getTop5() {
        // Faz um JOIN com a tabela de logs, conta as ocorrências e ordena
        $sql = "SELECT c.id, c.tipo, c.titulo, c.descricao, c.cover_image, c.arquivo, COUNT(l.id) as total_acessos
                FROM contents c
                LEFT JOIN access_logs l ON c.id = l.conteudo_id
                WHERE c.visivel = 1
                GROUP BY c.id
                ORDER BY total_acessos DESC
                LIMIT 5";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Cria um novo conteúdo (upload)
     */
    public function create(string $tipo, string $titulo, string $descricao, string $arquivo, ?string $cover_image, int $adminId) {
        $sql = "INSERT INTO contents (tipo, titulo, descricao, arquivo, cover_image, criado_por) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$tipo, $titulo, $descricao, $arquivo, $cover_image, $adminId]);
    
    }
}