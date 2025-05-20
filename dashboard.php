<?php
require_once 'config.php';
require_once 'classes/Sessao.php';

Sessao::validar();
$usuario = Sessao::getUsuario();
$isAdmin = Sessao::isAdmin();

if (!$usuario || !isset($usuario['nome'], $usuario['email'], $usuario['idioma'], $usuario['tema'])) {
    redirect('login.php');
}

// Determina idioma e tema do usuário
$tema = $_COOKIE['tema'] ?? $usuario['tema'] ?? 'claro';
$idioma = $_COOKIE['idioma'] ?? $usuario['idioma'] ?? 'pt';

// Carrega traduções
$traducoes = require 'idioma.php';
$t = $traducoes[$idioma] ?? $traducoes['pt'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            transition: background-color 0.3s, color 0.3s;
        }
        body.dark-mode {
            background-color: #212529;
            color: #f8f9fa;
        }
        .dark-mode .card,
        .dark-mode .list-group-item {
            background-color: #2c3034;
            border-color: #373b3e;
            color: #f8f9fa;
        }
        .dark-mode .btn-outline-primary {
            color: #0d6efd;
            border-color: #0d6efd;
        }
        .dark-mode .btn-outline-primary:hover {
            background-color: #0d6efd;
            color: #fff;
        }
        .profile-actions .btn {
            margin-top: 0.5rem;
        }
    </style>
</head>
<body class="<?= $tema === 'escuro' ? 'dark-mode' : '' ?>">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            <i class="bi bi-person-circle me-2"></i><?= $t['welcome'] ?>, <?= htmlspecialchars($usuario['nome']) ?>!
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3"><?= $t['your_profile'] ?></h5>

                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item">
                                <i class="bi bi-envelope-fill me-2 text-primary"></i>
                                <strong><?= $t['email'] ?>:</strong> <?= htmlspecialchars($usuario['email']) ?>
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-translate me-2 text-primary"></i>
                                <strong><?= $t['language'] ?>:</strong> <?= htmlspecialchars(ucfirst($usuario['idioma'])) ?>
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-palette-fill me-2 text-primary"></i>
                                <strong><?= $t['theme'] ?>:</strong> <?= htmlspecialchars(ucfirst($usuario['tema'])) ?>
                            </li>
                            <?php if ($isAdmin): ?>
                                <li class="list-group-item">
                                    <i class="bi bi-person-badge-fill me-2 text-primary"></i>
                                    <strong><?= $t['admin_type'] ?></strong>
                                </li>
                            <?php endif; ?>
                        </ul>

                        <div class="d-flex justify-content-end profile-actions">
                            <a href="editar_perfil.php" class="btn btn-outline-primary me-2">
                                <i class="bi bi-pencil-square me-1"></i> <?= $t['edit_profile'] ?>
                            </a>
                            <a href="logout.php" class="btn btn-danger">
                                <i class="bi bi-box-arrow-right me-1"></i> <?= $t['logout'] ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
