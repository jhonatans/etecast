<?php require_once BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 mt-5">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="/img/etecast_logo.png" alt="ETECast Logo" style="height: 80px;">
                    <h3 class="mt-3">Acessar ETECast</h3>
                </div>

                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>
                <?php if (isset($sucesso)): ?>
                    <div class="alert alert-success"><?php echo $sucesso; ?></div>
                <?php endif; ?>

                <form action="/login" method="POST">
                    <div class="mb-3">
                        <label for="matricula" class="form-label">Matrícula</label>
                        <input type="text" class="form-control" id="matricula" name="matricula" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="/register">Primeiro acesso?</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/Views/partials/footer.php'; ?>