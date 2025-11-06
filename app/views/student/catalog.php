<?php 
$pageTitle = 'Catálogo de Conteúdo';

require __DIR__ . '/../layouts/header.php'; 
?>

<div class="row mb-3">
    <div class="col">
        <h2>Catálogo de Conteúdo</h2>
        <p>Olá, <?php echo htmlspecialchars($studentName ?? 'Aluno'); ?>! Explore nossos materiais.</p>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    
    <?php if (empty($contents)): ?>
        <div class="col-12">
            <div class="alert alert-info">Nenhum conteúdo disponível no momento.</div>
        </div>
    <?php else: ?>
        <?php foreach ($contents as $content): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php 
                            $icon = 'bi-question-circle';
                            if ($content['tipo'] === 'video') $icon = 'bi-camera-video-fill text-danger';
                            if ($content['tipo'] === 'podcast') $icon = 'bi-mic-fill text-primary';
                            if ($content['tipo'] === 'pdf') $icon = 'bi-file-earmark-pdf-fill text-success';
                            ?>
                            <i class="bi <?php echo $icon; ?> me-2"></i>
                            <?php echo htmlspecialchars($content['titulo']); ?>
                        </h5>
                        
                        <p class="card-text text-muted">
                            <?php echo htmlspecialchars(substr($content['descricao'] ?? '', 0, 100)) . '...'; ?>
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0 text-end">
                        <a href="<?php echo BASE_URL . '/content/view/' . $content['id']; ?>" class="btn btn-primary" style="background-color: var(--ete-blue);">
                            Acessar Conteúdo
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php 

require __DIR__ . '/../layouts/footer.php'; 
?>