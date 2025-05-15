<?php
require_once 'config.php';
require_once 'classes/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        $idioma = $_POST['idioma'] ?? 'pt';
        $tema = $_POST['tema'] ?? 'claro';

        if (empty($nome) || empty($email) || empty($senha) || empty($confirmarSenha)) {
            throw new Exception('Todos os campos são obrigatórios!');
        }

        if ($senha !== $confirmarSenha) {
            throw new Exception('As senhas não coincidem!');
        }

        $usuarios = carregarUsuarios();
        foreach ($usuarios as $usuario) {
            if ($usuario['email'] === $email) {
                throw new Exception('Email já cadastrado!');
            }
        }

        $novoUsuario = new Usuario($nome, $email, $senha, $idioma, $tema);
        $novoUsuario->salvar();

        $_SESSION['registro_sucesso'] = true;
        redirect('login.php');
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
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Criar Conta</h2>
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['registro_sucesso'])): ?>
                            <div class="alert alert-success">Registro realizado com sucesso!</div>
                            <?php unset($_SESSION['registro_sucesso']); ?>
                        <?php endif; ?>
                        <form method="POST">
                            <!-- Campos do formulário -->
                            <button type="submit" class="btn btn-primary w-100">Registrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>