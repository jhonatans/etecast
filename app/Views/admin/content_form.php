<?php require_once BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0">Upload de Mídia</h4>
        </div>
        <div class="card-body">
            
            <div id="uploadAlert" class="alert d-none"></div>

            <form id="uploadForm">
                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" class="form-control" name="titulo" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <textarea class="form-control" name="descricao" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" name="tipo" required>
                        <option value="video">Vídeo (MP4)</option>
                        <option value="podcast">Podcast (MP3)</option>
                        <option value="pdf">Documento (PDF)</option>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Arquivo de Mídia</label>
                        <input class="form-control" type="file" name="arquivo" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Capa (Opcional)</label>
                        <input class="form-control" type="file" name="cover" accept="image/*">
                    </div>
                </div>

                <div class="progress mb-3 d-none" id="progressContainer" style="height: 25px;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                </div>

                <button type="submit" class="btn btn-primary" id="btnSubmit">
                    Iniciar Upload
                </button>
                <a href="javascript:history.back()" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Impede o reload da página

    let formData = new FormData(this);
    let xhr = new XMLHttpRequest();
    
    // Elementos da UI
    let btn = document.getElementById('btnSubmit');
    let progressContainer = document.getElementById('progressContainer');
    let progressBar = document.getElementById('progressBar');
    let alertBox = document.getElementById('uploadAlert');

    // Preparar UI
    btn.disabled = true;
    btn.innerText = "Enviando...";
    progressContainer.classList.remove('d-none');
    alertBox.classList.add('d-none');
    alertBox.className = 'alert d-none'; // Reseta classes de cor

    // Evento de Progresso
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            let percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressBar.innerText = percent + '%';
        }
    });

    // Evento de Conclusão
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            btn.disabled = false;
            btn.innerText = "Iniciar Upload";
            progressContainer.classList.add('d-none');

            try {
                let response = JSON.parse(xhr.responseText);
                
                alertBox.classList.remove('d-none');
                if (xhr.status === 200 && response.success) {
                    alertBox.classList.add('alert-success');
                    alertBox.innerText = "Upload realizado com sucesso! Redirecionando...";
                    setTimeout(() => {
                        // Redireciona para o dashboard correto baseado na URL atual
                        window.location.href = window.location.href.includes('/admin') ? '/admin/content' : '/dashboard';
                    }, 1500);
                } else {
                    throw new Error(response.error || "Erro desconhecido no servidor.");
                }
            } catch (err) {
                alertBox.classList.remove('d-none');
                alertBox.classList.add('alert-danger');
                alertBox.innerText = "Erro: " + (xhr.responseText || err.message);
            }
        }
    };

    // Enviar
    xhr.open('POST', '/upload/handler', true); 
    xhr.send(formData);
});
</script>

<?php require_once BASE_PATH . '/app/Views/partials/footer.php'; ?>