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

<ul class="nav nav-tabs nav-fill mb-4" id="contentTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="videos-tab" data-bs-toggle="tab" data-bs-target="#videos-panel" type="button" role="tab">
            <i class="bi bi-camera-video-fill text-danger me-2"></i> Vídeos (<?php echo count($videos); ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="podcasts-tab" data-bs-toggle="tab" data-bs-target="#podcasts-panel" type="button" role="tab">
            <i class="bi bi-mic-fill text-primary me-2"></i> Podcasts (<?php echo count($podcasts); ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="livros-tab" data-bs-toggle="tab" data-bs-target="#livros-panel" type="button" role="tab">
            <i class="bi bi-file-earmark-pdf-fill text-success me-2"></i> Livros (<?php echo count($livros); ?>)
        </button>
    </li>
</ul>

<?php
function renderContentCard($content) {
    // Define um ícone e capa padrão
    $icon = 'bi-question-circle';
    $capa_padrao = '/assets/img/etecast_logo.png';
    if ($content['tipo'] === 'video') $icon = 'bi-camera-video-fill text-danger';
    if ($content['tipo'] === 'podcast') $icon = 'bi-mic-fill text-primary';
    if ($content['tipo'] === 'pdf') $icon = 'bi-file-earmark-pdf-fill text-success';
    
    // Usa a capa do banco ou a padrão
    $capaUrl = $content['capa_url'] ? (BASE_URL . $content['capa_url']) : $capa_padrao;
?>
    <div class="col">
        <div class="card h-100 shadow-sm">
            <img src="<?php echo $capaUrl; ?>" class="card-img-top" alt="Capa de <?php echo htmlspecialchars($content['titulo']); ?>" style="height: 200px; object-fit: cover;">
            
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi <?php echo $icon; ?> me-2"></i>
                    <?php echo htmlspecialchars($content['titulo']); ?>
                </h5>
                <p class="card-text text-muted">
                    <?php 
                    $descricao = $content['descricao'] ?? '';
                    echo htmlspecialchars(substr($descricao, 0, 100)) . (strlen($descricao) > 100 ? '...' : ''); 
                    ?>
                </p>
            </div>
            <div class="card-footer bg-white border-0 text-end">
                <a href="<?php echo BASE_URL . '/content/view/' . $content['id']; ?>" class="btn btn-primary" style="background-color: var(--ete-blue);">
                    Acessar
                </a>
            </div>
        </div>
    </div>
<?php } ?>


<div class="tab-content" id="contentTabContent">
    
    <div class="tab-pane fade show active" id="videos-panel" role="tabpanel">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (empty($videos)): ?>
                <div class="col-12"><div class="alert alert-info">Nenhum vídeo disponível.</div></div>
            <?php else: ?>
                <?php foreach ($videos as $content) { renderContentCard($content); } ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="tab-pane fade" id="podcasts-panel" role="tabpanel">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (empty($podcasts)): ?>
                <div class="col-12"><div class="alert alert-info">Nenhum podcast disponível.</div></div>
            <?php else: ?>
                <?php foreach ($podcasts as $content) { renderContentCard($content); } ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="tab-pane fade" id="livros-panel" role="tabpanel">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (empty($livros)): ?>
                <div class="col-12"><div class="alert alert-info">Nenhum livro disponível.</div></div>
            <?php else: ?>
                <?php foreach ($livros as $content) { renderContentCard($content); } ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
require __DIR__ . '/../layouts/footer.php'; 
?>