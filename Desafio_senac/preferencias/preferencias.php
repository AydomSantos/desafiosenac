<?php
require_once 'config.php';
require_once 'classes/Sessao.php';
require_once 'classes/Usuario.php';

Sessao::validar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idioma = $_POST['idioma'] ?? 'pt';
    $tema = $_POST['tema'] ?? 'claro';
    
    try {
        $usuarios = carregarUsuarios();
        $usuarioId = Sessao::getUsuario()['id'];
        
        if (isset($usuarios[$usuarioId])) {
            $usuarios[$usuarioId]['idioma'] = $idioma;
            $usuarios[$usuarioId]['tema'] = $tema;
            salvarUsuarios($usuarios);
            
            // Atualizar sessão
            $_SESSION['usuario']['idioma'] = $idioma;
            $_SESSION['usuario']['tema'] = $tema;
            
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Usuário não encontrado');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
}
?>