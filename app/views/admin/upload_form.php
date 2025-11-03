<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ETECast - Upload de Conteúdo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --ete-blue: <?php echo $colors['primary-blue']; ?>;
            --ete-yellow: <?php echo $colors['ete-yellow']; ?>;
            --ete-green: <?php echo $colors['ete-green']; ?>;
            --ete-red: <?php echo $colors['ete-red']; ?>;
            --ete-white: <?php echo $colors['white']; ?>;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }
        .navbar {
            background-color: var(--ete-blue);
        }
        .navbar-brand {
            color: var(--ete-white) !important;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        .navbar-brand img {
            height: 30px; 
            margin-right: 10px;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
            background-color: var(--ete-white);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: var(--ete-blue);
            margin-bottom: 30px;
            text-align: center;
        }
        .form-label {
            font-weight: bold;
            color: var(--ete-blue);
        }
        .btn-primary {
            background-color: var(--ete-blue);
            border-color: var(--ete-blue);
        }
        .btn-primary:hover {
            background-color: darken(var(--ete-blue), 10%);
            border-color: darken(var(--ete-blue), 10%);
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/admin">
                <img src="<?php echo BASE_URL; ?>/assets/img/etecast_logo.png" alt="ETECast Logo"> ETECast Admin
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/admin/logout">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Upload de Novo Conteúdo</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/admin/content/upload" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Título do Conteúdo</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Descrição</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="content_type" class="form-label">Tipo de Conteúdo</label>
                <select class="form-select" id="content_type" name="content_type" required>
                    <option value="">Selecione...</option>
                    <option value="video">Vídeo</option>
                    <option value="podcast">Podcast (Áudio)</option>
                    <option value="pdf">Livro (PDF)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="content_file" class="form-label">Arquivo de Mídia</label>
                <input class="form-control" type="file" id="content_file" name="content_file" required>
            </div>
            <button type="submit" class="btn btn-primary">Fazer Upload</button>
        </form>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> ETECast. Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>