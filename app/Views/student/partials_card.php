<div class="col">
    <div class="card h-100 shadow-sm border-0">
        
        <img src="<?php echo ($item['cover_image']) ? '/media/' . $item['cover_image'] : '/img/etecast_logo.png'; ?>" 
             class="card-img-top" 
             alt="<?php echo htmlspecialchars($item['titulo']); ?>" 
             style="height: 180px; object-fit: cover;">

        <div class="card-body">
            <h5 class="card-title">
                <?php if ($item['tipo'] == 'video') echo '▶️'; ?>
                <?php if ($item['tipo'] == 'podcast') echo '🎧'; ?>
                <?php if ($item['tipo'] == 'pdf') echo '📄'; ?>
                
                <?php echo htmlspecialchars($item['titulo']); ?>
            </h5>
            <p class="card-text text-muted small">
                <?php echo htmlspecialchars($item['descricao'] ?? ''); ?>
            </p>
        </div>
        <div class="card-footer bg-white border-0">
            <a href="/content/<?php echo $item['id']; ?>" class="btn btn-outline-primary w-100">
                Acessar
            </a>
        </div>
    </div>
</div>