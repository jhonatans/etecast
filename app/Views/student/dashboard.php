<?php require_once BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="container">
    
    <?php if (!empty($top5)): ?>
    <div class="mb-5">
        <h4 class="mb-3 fw-bold" style="color: #ff9800;">🔥 Top 5 Mais Acessados</h4>
        <div class="row row-cols-1 row-cols-md-5 g-3">
            <?php foreach ($top5 as $item): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-warning" style="border-width: 1px;">
                        <div class="position-absolute top-0 start-0 bg-warning text-dark px-2 py-1 fw-bold rounded-bottom-end" style="z-index: 10;">
                            TOP
                        </div>
                        
                        <img src="<?php echo ($item['cover_image']) ? '/media/' . $item['cover_image'] : '/img/etecast_logo.png'; ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($item['titulo']); ?>" 
                             style="height: 120px; object-fit: cover;">
                        
                        <div class="card-body p-2">
                            <h6 class="card-title text-truncate" style="font-size: 0.95rem;">
                                <?php echo htmlspecialchars($item['titulo']); ?>
                            </h6>
                            <a href="/content/<?php echo $item['id']; ?>" class="btn btn-sm btn-warning w-100 stretched-link">Acessar</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <hr class="text-muted">
    <?php endif; ?>


    <div class="mb-5">
        <h4 class="mb-3" style="color: var(--etecast-blue); border-left: 5px solid var(--etecast-blue); padding-left: 10px;">
            📺 Vídeo Aulas
        </h4>
        <?php if (empty($videos)): ?>
            <p class="text-muted ms-3">Nenhum vídeo disponível no momento.</p>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($videos as $item): ?>
                    <?php include 'partials_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>


    <div class="mb-5">
        <h4 class="mb-3 text-success" style="border-left: 5px solid var(--etecast-green); padding-left: 10px;">
            🎙️ Podcasts
        </h4>
        <?php if (empty($podcasts)): ?>
            <p class="text-muted ms-3">Nenhum podcast disponível no momento.</p>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($podcasts as $item): ?>
                    <?php include 'partials_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>


    <div class="mb-5">
        <h4 class="mb-3 text-danger" style="border-left: 5px solid var(--etecast-red); padding-left: 10px;">
            📄 Materiais em PDF
        </h4>
        <?php if (empty($pdfs)): ?>
            <p class="text-muted ms-3">Nenhum PDF disponível no momento.</p>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($pdfs as $item): ?>
                    <?php include 'partials_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once BASE_PATH . '/app/Views/partials/footer.php'; ?>