<?php 
require_once BASE_PATH . '/app/Views/partials/header.php'; 
?>

<div class="card">
    <div class="card-body">
        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form action="/admin/content/add" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" required>
            </div>
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo de Mídia</label>
                <select class="form-select" id="tipo" name="tipo" required>
                    <option value="">Selecione...</option>
                    <option value="video">Vídeo (mp4)</option>
                    <option value="podcast">Podcast (mp3)</option>
                    <option value="pdf">PDF (pdf)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="arquivo" class="form-label">Arquivo de Mídia (Vídeo, PDF, etc)</label>
                <input class="form-control" type="file" id="arquivo" name="arquivo" required>
            </div>

            <div class="mb-3">
                <label for="cover" class="form-label">Imagem de Capa (JPG, PNG)</label>
                <input class="form-control" type="file" id="cover" name="cover">
                <small class="text-muted">Opcional, mas recomendado para o card.</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Salvar Conteúdo</button>
            <a href="/admin/content" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php 
require_once BASE_PATH . '/app/Views/partials/footer.php'; 
?>