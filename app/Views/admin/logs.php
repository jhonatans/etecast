<?php 
require_once BASE_PATH . '/app/Views/partials/header.php'; 
?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th scope="col">Data/Hora</th>
                        <th scope="col">Aluno</th>
                        <th scope="col">Ação</th>
                        <th scope="col">Conteúdo</th>
                        <th scope="col">IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Nenhum log registrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['criado_em']); ?></td>
                                <td><?php echo htmlspecialchars($log['aluno_nome'] ?? 'ID: ' . $log['aluno_id']); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php if ($log['acao'] == 'login') echo 'bg-info'; ?>
                                        <?php if ($log['acao'] == 'assistir') echo 'bg-secondary'; ?>
                                        <?php if ($log['acao'] == 'ler') echo 'bg-secondary'; ?>
                                        <?php if ($log['acao'] == 'ouvir') echo 'bg-secondary'; ?>
                                    ">
                                        <?php echo htmlspecialchars($log['acao']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['conteudo_titulo'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['ip_str'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/Views/partials/footer.php'; ?>