<?php
require_once 'config.php';
require_once 'classes/Sessao.php';
require_once 'classes/Usuario.php';

Sessao::validar();
header('Content-Type: application/json');

// Garante que o método é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

try {
    // Valores submetidos (com fallback)
    $idioma = $_POST['idioma'] ?? 'pt';
    $tema = $_POST['tema'] ?? 'claro';

    // Definição de valores válidos
    $idiomasPermitidos = ['pt', 'en', 'es'];
    $temasPermitidos = ['claro', 'escuro'];

    // Validações
    if (!in_array($idioma, $idiomasPermitidos)) {
        throw new Exception('Idioma inválido.');
    }

    if (!in_array($tema, $temasPermitidos)) {
        throw new Exception('Tema inválido.');
    }

    // Recupera usuário logado
    $usuario = Sessao::getUsuario();
    $usuarioId = $usuario['id'] ?? null;

    if (!$usuarioId) {
        throw new Exception('Usuário não identificado.');
    }

    $usuarios = carregarUsuarios();

    if (!isset($usuarios[$usuarioId])) {
        throw new Exception('Usuário não encontrado no banco.');
    }

    // Atualiza os dados persistidos
    $usuarios[$usuarioId]['idioma'] = $idioma;
    $usuarios[$usuarioId]['tema'] = $tema;
    salvarUsuarios($usuarios);

    // Atualiza sessão
    $_SESSION['usuario']['idioma'] = $idioma;
    $_SESSION['usuario']['tema'] = $tema;

    // Atualiza cookies (opcional e útil para persistência visual)
    setcookie('idioma', $idioma, time() + (86400 * 30), "/"); // 30 dias
    setcookie('tema', $tema, time() + (86400 * 30), "/");

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

