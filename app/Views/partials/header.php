<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo $titulo ?? 'ETECast'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css?version=<?php echo time(); ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<?php
// Define o estilo da barra de navegação baseado na sessão
$navClass = 'navbar-dark shadow-sm'; 
$navStyle = ''; 
$brandLink = '/'; 
$brandText = 'ETECast'; 

if (isset($_SESSION['admin_id'])) {
    // É um ADMIN
    $navStyle = 'background-color: var(--etecast-dark);'; 
    $brandLink = '/admin';
    $brandText = 'Admin ETECast';
} else {
    // É um ALUNO ou VISITANTE
    $navStyle = 'background-color: var(--etecast-blue);'; 
    $brandLink = (isset($_SESSION['aluno_id'])) ? '/dashboard' : '/'; 
    $brandText = 'ETECast';
}
?>

<nav class="navbar navbar-expand-lg <?php echo $navClass; ?>" style="<?php echo $navStyle; ?>">
  <div class="container">
    
    <a class="navbar-brand" href="<?php echo $brandLink; ?>">
        <img src="/img/etecast_logo.png" alt="ETECast Logo" style="height: 40px;"> <?php echo $brandText; ?>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
        
        <?php if (isset($_SESSION['aluno_id'])): ?>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item">
                    <a class="nav-link active" href="/dashboard">Dashboard</a>
                </li>
                
                <li class="nav-item ms-2 me-2">
                    <span class="text-white border border-light rounded px-2 py-1" style="font-size: 0.9rem; opacity: 0.9;">
                        Olá, <?php echo htmlspecialchars($_SESSION['aluno_nome'] ?? 'Aluno'); ?>
                    </span>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/logout" style="color: rgba(255,255,255,0.8);">Sair</a>
                </li>
            </ul>

        <?php elseif (isset($_SESSION['admin_id'])): ?>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/content">Conteúdos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/logs">Logs</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/logout">
                        Sair (<?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>)
                    </a>
                </li>
            </ul>

        <?php else: ?>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            </ul>
        <?php endif; ?>

    </div>
  </div>
</nav>

<main class="container mt-4 flex-grow-1">
    <h2 class="mb-4"><?php echo $titulo ?? ''; ?></h2>