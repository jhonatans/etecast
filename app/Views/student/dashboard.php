<?php require_once BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="container">
    <h3 class="mb-4">Conteúdos Disponíveis</h3>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php if (empty($conteudos)): ?>
            <div class="col">
                <p>Nenhum conteúdo disponível no momento.</p>
            </div>
        <?php else: ?>
            <?php foreach ($conteudos as $item): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0">
                        
                        <img src="<?php echo ($item['cover_image']) ? '/media/' . $item['cover_image'] : '/img/etecast_logo.png'; ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($item['titulo']); ?>" 
                             style="height: 200px; object-fit: cover;">

                        <div class="card-body">
                            <h5 class="card-title">
                                <?php if ($item['tipo'] == 'video') echo '▶️'; ?>
                                <?php if ($item['tipo'] == 'podcast') echo '🎧'; ?>
                                <?php if ($item['tipo'] == 'pdf') echo '📄'; ?>
                                <?php echo htmlspecialchars($item['titulo']); ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars($item['descricao'] ?? ''); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="/content/<?php echo $item['id']; ?>" class="btn btn-primary w-100">
                                Acessar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once BASE_PATH . '/app/Views/partials/footer.php'; ?>