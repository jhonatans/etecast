<?php require_once BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 mt-5">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3 class="mt-3">Criar Nova Senha</h3>
                    <p class="text-muted">Defina sua senha de acesso.</p>
                </div>

                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>

                <form action="/create-password" method="POST">
                    <div class="mb-3">
                        <label for="senha" class="form-label">Nova Senha (mín. 6 caracteres)</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha_confirm" class="form-label">Confirme a Senha</label>
                        <input type="password" class="form-control" id="senha_confirm" name="senha_confirm" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Salvar Senha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/Views/partials/footer.php'; ?>