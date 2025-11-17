<?php require_once BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="container">
    <h2 class="mb-3"><?php echo htmlspecialchars($conteudo['titulo']); ?></h2>
    <p class="lead"><?php echo htmlspecialchars($conteudo['descricao'] ?? ''); ?></p>
    <hr>

    <div class="row justify-content-center">
        <div class="col-12">
            
            <?php if ($conteudo['tipo'] == 'video'): ?>
                <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet" />
                
                <video
                    id="etecast-video"
                    class="video-js vjs-big-play-centered w-100 vjs-fluid vjs-16-9"
                    controls preload="auto" data-setup='{}'>
                    <source src="<?php echo $secureUrl; ?>" type="video/mp4" />
                </video>
                <script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
                <script>
                    videojs('etecast-video', {
                        controls: true,
                        controlBar: {
                            children: [
                                'playToggle', 'volumePanel', 'progressControl', 
                                'currentTimeDisplay', 'timeDivider', 'durationDisplay', 
                                'fullscreenToggle'
                            ]
                        }
                    });
                </script>

            <?php elseif ($conteudo['tipo'] == 'podcast'): ?>
                <div class="text-center p-4 bg-light rounded shadow-sm">
                    <audio controls class="w-100" controlsList="nodownload">
                        <source src="<?php echo $secureUrl; ?>" type="audio/mpeg">
                    </audio>
                </div>

            <?php elseif ($conteudo['tipo'] == 'pdf'): ?>
                <style>
                    #pdf-viewer-container {
                        width: 100%;
                        height: 800px;
                        border: 1px solid #ccc;
                        overflow: auto;
                    }
                </style>
                
                <iframe 
                    id="pdf-viewer-container"
                    src="/player/pdfjs/web/viewer.html" 
                    title="Visualizador de PDF"
                    frameborder="0">
                </iframe>
                
                <script>
                    const pdfUrl = '<?php echo $secureUrl; ?>';
                    
                    const iframe = document.getElementById('pdf-viewer-container');
                    
                    iframe.onload = function() {
                        iframe.contentWindow.PDFViewerApplication.open(pdfUrl);

                        var iframeWindow = iframe.contentWindow;
                        
                        iframeWindow.PDFViewerApplication.initializedPromise.then(function () {
                            iframeWindow.PDFViewerApplicationOptions.set('toolbar', {
                                download: false,
                                print: false
                            });
                            iframeWindow.PDFViewerApplicationOptions.set('sidebar', {
                                download: false
                            });
                        });
                    };
                </async>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/Views/partials/footer.php'; ?>