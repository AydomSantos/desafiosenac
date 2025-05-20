<?php
session_start();
require_once 'config.php';
require_once 'classes/Usuario.php';


function limpar_input($chave, $filtro = FILTER_SANITIZE_SPECIAL_CHARS) {
    return filter_input(INPUT_POST, $chave, $filtro) ?? '';
}

function manter_valor($chave) {
    return isset($_SESSION['form_data'][$chave]) ? htmlspecialchars($_SESSION['form_data'][$chave]) : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitiza e coleta os dados
        $nome = limpar_input('nome');
        $email = limpar_input('email', FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        $idioma = limpar_input('idioma');
        $tema = limpar_input('tema');

        $_SESSION['form_data'] = $_POST; // Mantém dados em caso de erro

        // Validações
        if (!$nome || !$email || !$senha || !$confirmarSenha) {
            throw new Exception('Todos os campos são obrigatórios.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido.');
        }

        if ($senha !== $confirmarSenha) {
            throw new Exception('As senhas não coincidem.');
        }

        if (strlen($senha) < 8) {
            throw new Exception('A senha deve ter no mínimo 8 caracteres.');
        }

        $usuarios = carregarUsuarios();
        if (!is_array($usuarios)) {
            throw new Exception('Erro ao carregar usuários.');
        }

        foreach ($usuarios as $usuario) {
            if (isset($usuario['email']) && $usuario['email'] === $email) {
                throw new Exception('Email já cadastrado.');
            }
        }

        // Criação e salvamento
        $novoUsuario = Usuario::criarNovoUsuario($nome, $email, $senha, $idioma, $tema);
        $novoUsuario->salvar();

        unset($_SESSION['form_data']);
        $_SESSION['registro_sucesso'] = true;
        header('Location: login.php');
        exit;

    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>
