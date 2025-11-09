<?php 

$pageTitle = htmlspecialchars($content['titulo']);

require __DIR__ . '/../layouts/header.php'; 
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-3"><?php echo htmlspecialchars($content['titulo']); ?></h1>
        <p class="lead text-muted"><?php echo nl2br(htmlspecialchars($content['descricao'] ?? '')); ?></p>
        <hr class="my-4">
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                
                <?php if ($content['tipo'] === 'video'): ?>
                    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
                    <video id="videoPlayer" controls class="w-100" style="max-height: 70vh; background-color: #000;"></video>
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var video = document.getElementById('videoPlayer');
                            var videoSrc = '<?php echo $streamingUrl; ?>';
                            
                            if (Hls.isSupported()) {
                                var hls = new Hls();
                                hls.loadSource(videoSrc);
                                hls.attachMedia(video);
                                hls.on(Hls.Events.MANIFEST_PARSED, function() {
                                    video.play();
                                });
                            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                                video.src = videoSrc;
                                video.addEventListener('loadedmetadata', function() {
                                    video.play();
                                });
                            }
                        });
                    </script>

                <?php elseif ($content['tipo'] === 'podcast'): ?>
                    <div class="p-4">
                        <audio controls class="w-100" autoplay>
                            <source src="<?php echo $streamingUrl; ?>" type="audio/mpeg">
                            Seu navegador não suporta o elemento de áudio.
                        </audio>
                    </div>

                <?php elseif ($content['tipo'] === 'pdf'): ?>
                    <div class="embed-responsive" style="height: 80vh; overflow-y: auto;">
                        <iframe src="<?php echo $streamingUrl; ?>" width="100%" height="100%" frameborder="0"></iframe>
                    </div>

                <?php endif; ?>

            </div>
        </div>
        <div class="text-center mt-3">
            <a href="<?php echo BASE_URL; ?>/" class="btn btn-secondary">&larr; Voltar ao Catálogo</a>
        </div>
    </div>
</div>

<?php 
require __DIR__ . '/../layouts/footer.php'; 
?>