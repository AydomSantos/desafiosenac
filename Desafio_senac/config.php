<?php
session_start();

define('BASE_URL', 'http://localhost/projeto_web_oo');
define('STORAGE_PATH', __DIR__ . '/storage/usuarios.json');

function carregarUsuarios() {
    if (!file_exists(STORAGE_PATH)) {
        file_put_contents(STORAGE_PATH, json_encode([]));
    }
    $data = file_get_contents(STORAGE_PATH);
    return json_decode($data, true) ?: [];
}

function salvarUsuarios($usuarios) {
    file_put_contents(STORAGE_PATH, json_encode($usuarios, JSON_PRETTY_PRINT));
}

function estaLogado() {
    return isset($_SESSION['usuario']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}
?>