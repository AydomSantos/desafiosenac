<?php
require_once 'config.php';
require_once 'classes/Usuario.php';
require_once 'classes/Administrador.php';
require_once 'classes/Sessao.php';

Sessao::validar(); 

$usuarioLogado = Sessao::getUsuario(); // Obtém os dados da sessão
$mensagemSucesso = '';
$erro = '';

// Carrega o objeto usuário completo para facilitar a manipulação
$todosUsuarios = carregarUsuarios();
$usuarioAtualDados = $todosUsuarios[$usuarioLogado['id']] ?? null;

if (!$usuarioAtualDados) {
    // Se por algum motivo o usuário não for encontrado no JSON, redireciona ou mostra erro
    redirect('dashboard.php'); // Ou lide com o erro de forma mais robusta
}

// Instancia o objeto usuário correto
if ($usuarioAtualDados['tipo'] === 'admin') {
    $objetoUsuario = new Administrador(
        $usuarioAtualDados['nome'],
        $usuarioAtualDados['email'],
        $usuarioAtualDados['senha'], // Passa o hash da senha
        $usuarioAtualDados['idioma'],
        $usuarioAtualDados['tema']
    );
} else {
    $objetoUsuario = new Usuario(
        $usuarioAtualDados['nome'],
        $usuarioAtualDados['email'],
        $usuarioAtualDados['senha'], // Passa o hash da senha
        $usuarioAtualDados['idioma'],
        $usuarioAtualDados['tema']
    );
}
$objetoUsuario->setId($usuarioAtualDados['id']);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $novaSenha = $_POST['nova_senha'];
        $confirmarNovaSenha = $_POST['confirmar_nova_senha'];
        $idioma = $_POST['idioma'];
        $tema = $_POST['tema'];

        if (empty($nome) || empty($email) || empty($idioma) || empty($tema)) {
            throw new Exception('Nome, email, idioma e tema são obrigatórios.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Formato de email inválido.');
        }

        // Atualiza nome e email no objeto usando os setters
        $objetoUsuario->setNome($nome);
        $objetoUsuario->setEmail($email);
        $objetoUsuario->setIdioma($idioma);
        $objetoUsuario->setTema($tema);

        // Verifica se o email já existe para outro usuário
        foreach ($todosUsuarios as $id => $u) {
            // Add this check to ensure $u is an array and 'email' key exists
            if (is_array($u) && isset($u['email'])) {
                if ($id !== $objetoUsuario->getId() && $u['email'] === $email) {
                    throw new Exception('Este email já está em uso por outro usuário.');
                }
            }
        }

        // Lógica para atualização de senha
        if (!empty($novaSenha)) {
            if (strlen($novaSenha) < 8) {
                throw new Exception('A nova senha deve ter pelo menos 8 caracteres.');
            }
            if ($novaSenha !== $confirmarNovaSenha) {
                throw new Exception('As novas senhas não coincidem.');
            }
            $objetoUsuario->setSenha($novaSenha); // setSenha deve hashear a nova senha
        }

        // Salva as alterações
        $objetoUsuario->salvar(); // O método salvar deve atualizar o JSON

        // Atualiza os cookies de preferência
        setcookie('idioma', $idioma, time() + (86400 * 30), "/"); // Cookie por 30 dias
        setcookie('tema', $tema, time() + (86400 * 30), "/");

        // Atualiza a sessão com os novos dados
        // É importante recarregar os dados do usuário após salvar para garantir que a sessão tenha os dados mais recentes
        $dadosAtualizadosParaSessao = carregarUsuarios()[$objetoUsuario->getId()];
        Sessao::atualizarUsuario($dadosAtualizadosParaSessao);


        $mensagemSucesso = 'Perfil atualizado com sucesso!';
        // Recarrega os dados do usuário para exibir no formulário após a atualização
        $usuarioLogado = Sessao::getUsuario();


    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Para preencher o formulário e aplicar o tema/idioma da página
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { transition: background-color 0.3s, color 0.3s; }
        body.dark-mode { background-color: #212529; color: #f8f9fa; }
        .dark-mode .card { background-color: #2c3034; border-color: #373b3e; }
        .dark-mode .form-control, .dark-mode .form-select {
            background-color: #343a40;
            color: #f8f9fa;
            border-color: #495057;
        }
        .dark-mode .form-control::placeholder { color: #adb5bd; }
        .dark-mode .form-control:focus, .dark-mode .form-select:focus {
            background-color: #343a40;
            color: #f8f9fa;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .password-container { position: relative; }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 70%; /* Ajustado para alinhar melhor com o input dentro do form-group */
            transform: translateY(-50%);
            z-index: 100;
        }
         .dark-mode .password-toggle { color: #f8f9fa; }
    </style>
</head>
<body class="<?= $temaPagina === 'escuro' ? 'dark-mode' : '' ?>">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Perfil</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($mensagemSucesso): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($mensagemSucesso) ?></div>
                        <?php endif; ?>
                        <?php if ($erro): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="editar_perfil.php" class="needs-validation" novalidate>
                            <!-- Nome -->
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome"
                                       value="<?= htmlspecialchars($usuarioLogado['nome'] ?? '') ?>" required>
                                <div class="invalid-feedback">Por favor, insira seu nome.</div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= htmlspecialchars($usuarioLogado['email'] ?? '') ?>" required>
                                <div class="invalid-feedback">Por favor, insira um email válido.</div>
                            </div>

                            <hr>
                            <p class="text-muted small">Deixe os campos de senha em branco se não desejar alterá-la.</p>

                            <!-- Nova Senha -->
                            <div class="mb-3 password-container">
                                <label for="nova_senha" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="nova_senha" name="nova_senha" minlength="8">
                                <span class="password-toggle" onclick="togglePassword('nova_senha')">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                                <div class="invalid-feedback">A nova senha deve ter pelo menos 8 caracteres.</div>
                            </div>

                            <!-- Confirmar Nova Senha -->
                            <div class="mb-3 password-container">
                                <label for="confirmar_nova_senha" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirmar_nova_senha" name="confirmar_nova_senha">
                                <span class="password-toggle" onclick="togglePassword('confirmar_nova_senha')">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                                <div class="invalid-feedback">As senhas devem coincidir.</div>
                            </div>
                            <hr>

                            <!-- Idioma -->
                            <div class="mb-3">
                                <label for="idioma" class="form-label">Idioma</label>
                                <select class="form-select" id="idioma" name="idioma" required>
                                    <option value="pt" <?= ($usuarioLogado['idioma'] ?? 'pt') === 'pt' ? 'selected' : '' ?>>Português</option>
                                    <option value="en" <?= ($usuarioLogado['idioma'] ?? 'pt') === 'en' ? 'selected' : '' ?>>Inglês</option>
                                </select>
                            </div>

                            <!-- Tema -->
                            <div class="mb-4">
                                <label for="tema" class="form-label">Tema</label>
                                <select class="form-select" id="tema" name="tema" required>
                                    <option value="claro" <?= ($usuarioLogado['tema'] ?? 'claro') === 'claro' ? 'selected' : '' ?>>Claro</option>
                                    <option value="escuro" <?= ($usuarioLogado['tema'] ?? 'claro') === 'escuro' ? 'selected' : '' ?>>Escuro</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-secondary me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação Bootstrap
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })();

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                field.type = "password";
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        }
    </script>
</body>
</html>