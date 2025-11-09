<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ETECast - Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --ete-blue: #4285F4;
            --ete-yellow: #FFC107;
            --ete-green: #4CAF50;
            --ete-red: #F44336;
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
    </style>
</head>
<body>
    <div class="login-container">
        <img src="<?php echo BASE_URL; ?>/assets/img/etecast_logo.png" alt="ETECast Logo">
        <h1>Login do Administrador</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/admin/login" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Usuário</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
    </div>
</body>
</html>