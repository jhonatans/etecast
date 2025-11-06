<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'ETECast'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --ete-blue: #4285F4; /* Cor principal da logo */
            --ete-yellow: #FFC107;
            --ete-green: #4CAF50;
            --ete-red: #F44336;
            --ete-white: #FFFFFF;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
        }
        .navbar {
            background-color: var(--ete-blue);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        .navbar-nav .nav-link {
            color: var(--ete-white) !important;
            font-weight: 500;
        }
        .navbar-nav .nav-link:hover {
            color: rgba(255,255,255,0.8) !important;
        }
        .main-container {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #6c757d;
            padding: 20px 0;
            background-color: #e9ecef;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/">
            <img src="<?php echo BASE_URL; ?>/assets/img/etecast_logo.png" alt="ETECast Logo"> ETECast
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/">Catálogo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/logout">Sair (<?php echo htmlspecialchars($studentName ?? 'Aluno'); ?>)</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container main-container">