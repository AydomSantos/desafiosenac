<?php
require_once 'config.php';
require_once 'classes/Usuario.php';
require_once 'classes/Administrador.php';
require_once 'classes/Sessao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            throw new Exception('Preencha todos os campos!');
        }

        $usuarios = carregarUsuarios();
        $usuarioEncontrado = null;

        foreach ($usuarios as $usuario) {
            if ($usuario['email'] === $email) {
                if ($usuario['tipo'] === 'admin') {
                    $usuarioEncontrado = new Administrador(
                        $usuario['nome'],
                        $usuario['email'],
                        $usuario['senha'], // Senha hash
                        $usuario['idioma'],
                        $usuario['tema']
                    );
                } else {
                    $usuarioEncontrado = new Usuario(
                        $usuario['nome'],
                        $usuario['email'],
                        $usuario['senha'], // Senha hash
                        $usuario['idioma'],
                        $usuario['tema']
                    );
                }
                $usuarioEncontrado->id = $usuario['id'];
                break;
            }
        }

        if (!$usuarioEncontrado) {
            throw new Exception('Usuário não encontrado!');
        }

        if (!$usuarioEncontrado->verificarSenha($senha)) {
            throw new Exception('Senha incorreta!');
        }

        Sessao::iniciar($usuarioEncontrado);
        redirect('dashboard.php');
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Login</h2>
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="register.php">Criar uma conta</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>