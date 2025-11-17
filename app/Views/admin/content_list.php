<?php 
require_once BASE_PATH . '/app/Views/partials/header.php'; 
?>

<div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Seus Conteúdos</span>
        <a href="/admin/content/add" class="btn btn-primary btn-sm">
            Adicionar Novo +
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">Título</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Caminho do Arquivo</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($conteudos)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhum conteúdo cadastrado ainda.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($conteudos as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['titulo']); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php if ($item['tipo'] == 'video') echo 'bg-danger'; ?>
                                        <?php if ($item['tipo'] == 'podcast') echo 'bg-success'; ?>
                                        <?php if ($item['tipo'] == 'pdf') echo 'bg-warning text-dark'; ?>
                                    ">
                                        <?php echo htmlspecialchars($item['tipo']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($item['arquivo']); ?></td>
                                <td>
                                    <a href="#" class="btn btn-secondary btn-sm disabled">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/Views/partials/footer.php'; ?>