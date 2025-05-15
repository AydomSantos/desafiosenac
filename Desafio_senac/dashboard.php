<?php
require_once 'config.php';
require_once 'classes/Sessao.php';

Sessao::validar();
$usuario = Sessao::getUsuario();
$isAdmin = Sessao::isAdmin();
$tema = $_COOKIE['tema'] ?? 'claro';
?>

<!DOCTYPE html>
<html lang="<?= $_COOKIE['idioma'] ?? 'pt' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body.dark-mode {
            background-color: #212529;
            color: #f8f9fa;
        }
        .dark-mode .card {
            background-color: #2c3034;
            border-color: #373b3e;
        }
    </style>
</head>
<body class="<?= $tema === 'escuro' ? 'dark-mode' : '' ?>">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h3>Bem-vindo, <?= htmlspecialchars($usuario['nome']) ?>!</h3>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Seu Perfil</h5>
                        <p class="card-text">
                            <strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?><br>
                            <strong>Idioma:</strong> <?= htmlspecialchars($usuario['idioma']) ?><br>
                            <strong>Tema:</strong> <?= htmlspecialchars($usuario['tema']) ?><br>
                            <?php if ($isAdmin)): ?>
                                <strong>Tipo:</strong> Administrador<br>
                            <?php endif; ?>
                        </p>
                        <a href="logout.php" class="btn btn-danger">Sair</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>