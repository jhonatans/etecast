<?php

namespace App\Models;

class AccessLog extends BaseModel {

    // Registra um log
    public function log(int $alunoId, int $conteudoId = null, string $acao, string $ip, string $agente) {
        $ipBin = @inet_pton($ip); 
        if ($ipBin === false) $ipBin = null;

        $sql = "INSERT INTO access_logs (aluno_id, conteudo_id, acao, ip, agente) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$alunoId, $conteudoId, $acao, $ipBin, $agente]);
    }

    // Lista os logs para o painel admin
    public function listLogs(int $limit = 50) {
        $sql = "SELECT l.*, s.nome as aluno_nome, c.titulo as conteudo_titulo, INET6_NTOA(l.ip) as ip_str
                FROM access_logs l
                LEFT JOIN students s ON l.aluno_id = s.id
                LEFT JOIN contents c ON l.conteudo_id = c.id
                ORDER BY l.criado_em DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}