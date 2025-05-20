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
