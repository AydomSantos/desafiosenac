<?php
require_once 'config.php';
require_once 'classes/Usuario.php';
require_once 'classes/Administrador.php';
require_once 'classes/Sessao.php';

Sessao::validar();

$usuarioLogado = Sessao::getUsuario();
$mensagemSucesso = '';
$erro = '';

// Carrega todos os usuários (de JSON, DB, etc.)
$todosUsuarios = function_exists('carregarUsuarios') ? carregarUsuarios() : [];
$usuarioAtualDados = $todosUsuarios[$usuarioLogado['id']] ?? null;

if (!$usuarioAtualDados) {
    header("Location: dashboard.php");
    exit;
}

// Instancia o objeto do tipo certo
if ($usuarioAtualDados['tipo'] === 'admin') {
    $objetoUsuario = new Administrador(
        $usuarioAtualDados['nome'],
        $usuarioAtualDados['email'],
        $usuarioAtualDados['senha'],
        $usuarioAtualDados['idioma'],
        $usuarioAtualDados['tema']
    );
} else {
    $objetoUsuario = new Usuario(
        $usuarioAtualDados['nome'],
        $usuarioAtualDados['email'],
        $usuarioAtualDados['senha'],
        $usuarioAtualDados['idioma'],
        $usuarioAtualDados['tema']
    );
}
$objetoUsuario->setId($usuarioAtualDados['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = trim($_POST['nome'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarNovaSenha = $_POST['confirmar_nova_senha'] ?? '';
        $idioma = $_POST['idioma'] ?? '';
        $tema = $_POST['tema'] ?? '';

        if (empty($nome) || empty($email) || empty($idioma) || empty($tema)) {
            throw new Exception('Nome, email, idioma e tema são obrigatórios.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Formato de email inválido.');
        }

        // Verifica duplicidade de email
        foreach ($todosUsuarios as $id => $u) {
            if (is_array($u) && isset($u['email'])) {
                if ($id !== $objetoUsuario->getId() && strtolower($u['email']) === $email) {
                    throw new Exception('Este email já está em uso por outro usuário.');
                }
            }
        }

        // Atualiza dados
        $objetoUsuario->setNome($nome);
        $objetoUsuario->setEmail($email);
        $objetoUsuario->setIdioma($idioma);
        $objetoUsuario->setTema($tema);

        // Atualiza senha se fornecida
        if (!empty($novaSenha)) {
            if (strlen($novaSenha) < 8) {
                throw new Exception('A nova senha deve ter pelo menos 8 caracteres.');
            }
            if ($novaSenha !== $confirmarNovaSenha) {
                throw new Exception('As novas senhas não coincidem.');
            }
            $objetoUsuario->setSenha($novaSenha); // deve aplicar hash internamente
        }

        // Salvar
        $objetoUsuario->salvar();

        // Cookies
        setcookie('idioma', $idioma, time() + (86400 * 30), "/");
        setcookie('tema', $tema, time() + (86400 * 30), "/");

        // Atualizar sessão
        $usuariosAtualizados = carregarUsuarios();
        $dadosAtualizados = $usuariosAtualizados[$objetoUsuario->getId()] ?? null;

        if ($dadosAtualizados) {
            Sessao::atualizarUsuario($dadosAtualizados);
            $usuarioLogado = Sessao::getUsuario();
        }

        $mensagemSucesso = 'Perfil atualizado com sucesso!';
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Idioma e tema da página
$idiomaPagina = $_COOKIE['idioma'] ?? $usuarioLogado['idioma'] ?? 'pt';
$temaPagina = $_COOKIE['tema'] ?? $usuarioLogado['tema'] ?? 'claro';
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idiomaPagina) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dark-mode .btn-outline-primary {
            color: #0d6efd;
            border-color: #0d6efd;
        }
        .dark-mode .btn-outline-primary:hover {
            background-color: #0d6efd;
            color: #fff;
        }
    </style>
</head>
<body class="<?= $temaPagina === 'escuro' ? 'dark-mode' : ''
?>">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Editar Perfil</h2>
                        
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif;
                        if (isset($mensagemSucesso)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($mensagemSucesso) ?></div>
                        <?php endif;
                        ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <!-- Nome -->
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?= htmlspecialchars($objetoUsuario->getNome()) ?>" required>
                                <div class="invalid-feedback">
                                    Por favor, insira seu nome.
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($objetoUsuario->getEmail()) ?>" required>
                                <div class="invalid-feedback">
                                    Por favor, insira um email válido.
                                </div>
                            </div>
                            
                            <!-- Senha -->
                            <div class="mb-3 password-container">
                                <label for="nova_senha" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="nova_senha" name="nova_senha">
                                <span class="password-toggle" onclick="togglePassword('nova_senha', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                                <div class="invalid-feedback">
                                    A senha deve ter no mínimo 8 caracteres.
                                </div>
                            </div>
                            
                            <!-- Confirmar Nova Senha -->
                            <div class="mb-3 password-container">
                                <label for="confirmar_nova_senha" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirmar_nova_senha" name="confirmar_nova_senha">
                                <span class="password-toggle" onclick="togglePassword('confirmar_nova_senha', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                                <div class="invalid-feedback">
                                    As senhas devem coincidir.
                                </div>
                            </div>
                            
                            <!-- Idioma -->
                            <div class="mb-3">
                                <label for="idioma" class="form-label">Idioma</label>
                                <select class="form-select" id="idioma" name="idioma" required>
                                    <option value="pt" <?= $objetoUsuario->getIdioma() === 'pt' ? 'selected' : '' ?>>Português</option>
                                    <option value="en" <?= $objetoUsuario->getIdioma() === 'en' ? 'selected' : '' ?>>English</option>
                                </select>
                            </div>
                            
                            <!-- Tema -->
                            <div class="mb-3">
                                <label for="tema" class="form-label">Tema</label>
                                <select class="form-select" id="tema" name="tema" required>
                                    <option value="claro" <?= $objetoUsuario->getTema() === 'claro' ? 'selected' : '' ?>>Claro</option>
                                    <option value="escuro" <?= $objetoUsuario->getTema() === 'escuro' ? 'selected' : '' ?>>Escuro</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2">Salvar</button>
                            
                            <div class="mt-3 text-center">
                                <a href="dashboard.php">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>











