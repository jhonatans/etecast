<?php require_once BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 mt-5">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3 class="mt-3">Primeiro Acesso</h3>
                    <p class="text-muted">Confirme seus dados para criar sua senha.</p>
                </div>

                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>

                <form action="/register" method="POST">
                    <div class="mb-3">
                        <label for="matricula" class="form-label">Matrícula</label>
                        <input type="text" class="form-control" id="matricula" name="matricula" required>
                    </div>
                    <div class="mb-3">
                        <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                        <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Verificar</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="/">Voltar ao Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/Views/partials/footer.php'; ?>