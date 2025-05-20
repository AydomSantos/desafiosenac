<?php
// Inicia a sessão de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define a URL base do projeto (ajuste conforme necessário)
define('BASE_URL', 'http://localhost/projeto_web_oo');

// Caminho absoluto para o arquivo de usuários
define('STORAGE_PATH', __DIR__ . '/storage/usuarios.json');

/**
 * Carrega os usuários do arquivo JSON.
 *
 * @return array
 */
function carregarUsuarios(): array {
    if (!file_exists(STORAGE_PATH)) {
        file_put_contents(STORAGE_PATH, json_encode([]));
    }

    $data = file_get_contents(STORAGE_PATH);

    $usuarios = json_decode($data, true);

    return is_array($usuarios) ? $usuarios : [];
}

/**
 * Salva o array de usuários no arquivo JSON.
 *
 * @param array $usuarios
 * @return void
 */
function salvarUsuarios(array $usuarios): void {
    file_put_contents(STORAGE_PATH, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Verifica se um usuário está logado.
 *
 * @return bool
 */
function estaLogado(): bool {
    return isset($_SESSION['usuario']) && is_array($_SESSION['usuario']);
}

/**
 * Redireciona para uma URL.
 *
 * @param string $url
 * @return void
 */
function redirect(string $url): void {
    header("Location: $url");
    exit();
}
