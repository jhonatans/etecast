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
                    <script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.8"></script>
                    <video id="videoPlayer" controls class="w-100" style="max-height: 70vh; background-color: #000;"></video>
                    
                    <!-- <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var video = document.getElementById('videoPlayer');
                            
                            // Estas variáveis DEVEM ser passadas pelo StudentController
                            var manifestBaseUrl = '<?php echo $manifestBaseUrl ?? ''; // URL SEM o token ?>';
                            var token = '<?php echo $token ?? ''; // O token puro ?>';
                            var streamingUrlWithToken = '<?php echo $streamingUrl ?? ''; // URL COM o token (para Safari) ?>';
                            
                            // Se o token estiver vazio, é porque o controller falhou. Pare aqui.
                            if (!manifestBaseUrl || !token) {
                                console.error("ERRO CRÍTICO: As variáveis 'manifestBaseUrl' ou 'token' não foram passadas pelo PHP.");
                                return;
                            }
                            
                            if (Hls.isSupported()) {
                                console.log("HLS.js é suportado. Configurando com fetchSetup...");
                                
                                var hls = new Hls({
                                    /**
                                     * ESTA É A CORREÇÃO PRINCIPAL
                                     */
                                    fetchSetup: function(context, init) {
                                        var url;
                                        try {
                                            url = new URL(context.url);
                                        } catch (e) {
                                            url = new URL(context.url, manifestBaseUrl);
                                        }
                                        url.searchParams.set('t', token);
                                        context.url = url.toString();
                                        return init; // Continua com a requisição
                                    }
                                });

                                hls.loadSource(manifestBaseUrl);
                                hls.attachMedia(video);
                                hls.on(Hls.Events.MANIFEST_PARSED, function() {
                                    video.play();
                                });
                                hls.on(Hls.Events.ERROR, function(event, data) {
                                    if (data.fatal) {
                                        console.error('Erro fatal no HLS.js:', data.type, data.details, data.url);
                                    }
                                });

                            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                                console.log("HLS.js não suportado, usando player nativo (Safari).");
                                video.src = streamingUrlWithToken;
                                video.addEventListener('loadedmetadata', function() {
                                    video.play();
                                });
                            }
                        });
                    </script> -->

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var video = document.getElementById('videoPlayer');
                            
                            var manifestBaseUrl = '<?php echo $manifestBaseUrl ?? ''; ?>';
                            var token = '<?php echo $token ?? ''; ?>';
                            var streamingUrlWithToken = '<?php echo $streamingUrl ?? ''; ?>';

                            // 1. Verificação (como antes)
                            if (!manifestBaseUrl || !token) {
                                console.error("ERRO CRÍTICO: 'manifestBaseUrl' ou 'token' não foram passadas pelo PHP.");
                                return;
                            }
                            
                            if (Hls.isSupported()) {
                                console.log("HLS.js é suportado. Configurando...");
                                
                                // 2. Configuração (agora VAZIA, sem fetchSetup)
                                var hls = new Hls();

                                // 3. A MÁGICA (A CORREÇÃO UNIVERSAL)
                                // Esta função anexa o token à URL de forma segura
                                function appendAuthToken(url) {
                                    try {
                                        let parsedUrl = new URL(url, manifestBaseUrl); // Resolve URL relativa/absoluta
                                        parsedUrl.searchParams.set('t', token);
                                        return parsedUrl.toString();
                                    } catch (e) {
                                        console.error("Erro ao construir URL com token", e);
                                        return url; // Retorna a URL original em caso de falha
                                    }
                                }

                                // 4. Intercepta o MANIFESTO (.m3u8) antes de carregar
                                hls.on(Hls.Events.MANIFEST_LOADING, function(event, data) {
                                    data.url = appendAuthToken(data.url);
                                    console.log("MANIFEST_LOADING: URL com token:", data.url);
                                });

                                // 5. Intercepta CADA SEGMENTO (.ts) antes de carregar
                                hls.on(Hls.Events.FRAG_LOADING, function(event, data) {
                                    // 'data.frag.url' é o caminho do segmento
                                    data.frag.url = appendAuthToken(data.frag.url);
                                    console.log("FRAG_LOADING: URL com token:", data.frag.url);
                                });

                                // 6. Carrega o vídeo (como antes)
                                hls.loadSource(manifestBaseUrl);
                                hls.attachMedia(video);
                                
                                hls.on(Hls.Events.MANIFEST_PARSED, function() {
                                    video.play();
                                });

                                hls.on(Hls.Events.ERROR, function(event, data) {
                                    if (data.fatal) {
                                        console.error('Erro fatal no HLS.js:', data.type, data.details, data.url);
                                    }
                                });

                            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                                // Fallback para Safari (sempre esteve correto)
                                console.log("HLS.js não suportado, usando player nativo (Safari).");
                                video.src = streamingUrlWithToken;
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