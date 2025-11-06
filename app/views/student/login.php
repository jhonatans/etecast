<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ETECast - Login do Aluno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --ete-blue: #4285F4;
            --ete-white: #FFFFFF;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            background-color: var(--ete-white);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .login-container img {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }
        h1 {
            color: var(--ete-blue);
            margin-bottom: 30px;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .form-label {
            font-weight: bold;
            color: var(--ete-blue);
            text-align: left;
            width: 100%;
        }
        .btn-primary {
            background-color: var(--ete-blue);
            border-color: var(--ete-blue);
            width: 100%;
            font-weight: bold;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            text-align: left;
        }
        /* Estilo para abas */
        .nav-tabs .nav-link {
            color: var(--ete-blue);
        }
        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="<?php echo BASE_URL; ?>/assets/img/etecast_logo.png" alt="ETECast Logo">
        <h1>Portal do Aluno</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs nav-fill mb-3" id="loginTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-panel" type="button" role="tab">Login</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="primeiro-acesso-tab" data-bs-toggle="tab" data-bs-target="#primeiro-acesso-panel" type="button" role="tab">Primeiro Acesso</button>
            </li>
        </ul>

        <div class="tab-content" id="loginTabContent">
            
            <div class="tab-pane fade show active" id="login-panel" role="tabpanel">
                <form action="<?php echo BASE_URL; ?>/login" method="POST">
                    <div class="mb-3">
                        <label for="matricula-login" class="form-label">Matrícula</label>
                        <input type="text" class="form-control" id="matricula-login" name="matricula" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </form>
            </div>

            <div class="tab-pane fade" id="primeiro-acesso-panel" role="tabpanel">
                <form action="<?php echo BASE_URL; ?>/login" method="POST">
                    <p class="text-muted small">Use sua matrícula e data de nascimento para criar sua senha.</p>
                    <div class="mb-3">
                        <label for="matricula-primeiro" class="form-label">Matrícula</label>
                        <input type="text" class="form-control" id="matricula-primeiro" name="matricula" required>
                    </div>
                    <div class="mb-3">
                        <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                        <input type="text" class="form-control" id="data_nascimento" name="data_nascimento" placeholder="DD/MM/AAAA" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Continuar</button>
                </form>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>