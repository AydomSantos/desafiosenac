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
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: <?= $temaPagina === 'escuro' ? '#121212' : '#f4f4f4' ?>;
            color: <?= $temaPagina === 'escuro' ? '#ffffff' : '#000000' ?>;
            padding: 20px;
        }
        form {
            background: <?= $temaPagina === 'escuro' ? '#1e1e1e' : '#ffffff' ?>;
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 15px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 20px;
            background-color: #007BFF;
            border: none;
            padding: 10px 20px;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .mensagem {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .erro {
            background-color: #ffcccc;
            color: #a00;
        }
        .sucesso {
            background-color: #ccffcc;
            color: #080;
        }
    </style>
</head>
<body>

<h2>Editar Perfil</h2>

<?php if (!empty($erro)): ?>
    <div class="mensagem erro"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<?php if (!empty($mensagemSucesso)): ?>
    <div class="mensagem sucesso"><?= htmlspecialchars($mensagemSucesso) ?></div>
<?php endif; ?>

<form method="POST" action="">
    <label for="nome">Nome:</label>
    <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($usuarioLogado['nome']) ?>" required>

    <label for="email">E-mail:</label>
    <input type="email" name="email" id="email" value="<?= htmlspecialchars($usuarioLogado['email']) ?>" required>

    <label for="nova_senha">Nova Senha:</label>
    <input type="password" name="nova_senha" id="nova_senha" placeholder="Deixe em branco para não alterar">

    <label for="confirmar_nova_senha">Confirmar Nova Senha:</label>
    <input type="password" name="confirmar_nova_senha" id="confirmar_nova_senha" placeholder="Confirme a nova senha">

    <label for="idioma">Idioma:</label>
    <select name="idioma" id="idioma" required>
        <option value="pt" <?= $usuarioLogado['idioma'] === 'pt' ? 'selected' : '' ?>>Português</option>
        <option value="en" <?= $usuarioLogado['idioma'] === 'en' ? 'selected' : '' ?>>Inglês</option>
        <option value="es" <?= $usuarioLogado['idioma'] === 'es' ? 'selected' : '' ?>>Espanhol</option>
    </select>

    <label for="tema">Tema:</label>
    <select name="tema" id="tema" required>
        <option value="claro" <?= $usuarioLogado['tema'] === 'claro' ? 'selected' : '' ?>>Claro</option>
        <option value="escuro" <?= $usuarioLogado['tema'] === 'escuro' ? 'selected' : '' ?>>Escuro</option>
    </select>

    <button type="submit">Salvar Alterações</button>
</form>

</body>
</html>
