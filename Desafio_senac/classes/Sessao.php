<?php
require_once __DIR__ . '/../config.php'; 

class Sessao {
    public static function iniciar($usuario) {
        // Certifique-se de que a sessão foi iniciada (geralmente em config.php)
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); 
        }

        $_SESSION['usuario'] = [
            'id' => $usuario->getId(),
            'nome' => $usuario->getNome(),
            'email' => $usuario->getEmail(),
            'idioma' => $usuario->getIdioma(),
            'tema' => $usuario->getTema(),
            'tipo' => ($usuario instanceof Administrador) ? 'admin' : 'usuario'
        ];

        setcookie('idioma', $usuario->getIdioma(), time() + (86400 * 30), "/"); 
        setcookie('tema', $usuario->getTema(), time() + (86400 * 30), "/");   
    }

    public static function encerrar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        // Expira os cookies
        setcookie('idioma', '', time() - 3600, "/");
        setcookie('tema', '', time() - 3600, "/");
    }

    public static function validar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['usuario'])) {
            redirect('login.php'); 
        }
    }

    public static function getUsuario() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['usuario'] ?? null;
    }

    public static function isAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['usuario']['tipo']) && $_SESSION['usuario']['tipo'] === 'admin';
    }

    /**
     * Atualiza os dados do usuário na sessão e os cookies de preferência.
     * @param array $novosDadosUsuario Array contendo os novos dados do usuário.
     * Espera-se que contenha chaves como 'nome', 'email', 'idioma', 'tema', 'tipo'.
     */
    public static function atualizarUsuario(array $novosDadosUsuario) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['usuario'])) {
            if (isset($novosDadosUsuario['nome'])) {
                $_SESSION['usuario']['nome'] = $novosDadosUsuario['nome'];
            }
            if (isset($novosDadosUsuario['email'])) {
                $_SESSION['usuario']['email'] = $novosDadosUsuario['email'];
            }
            if (isset($novosDadosUsuario['idioma'])) {
                $_SESSION['usuario']['idioma'] = $novosDadosUsuario['idioma'];
                setcookie('idioma', $novosDadosUsuario['idioma'], time() + (86400 * 30), "/");
            }
            if (isset($novosDadosUsuario['tema'])) {
                $_SESSION['usuario']['tema'] = $novosDadosUsuario['tema'];
                setcookie('tema', $novosDadosUsuario['tema'], time() + (86400 * 30), "/");
            }
            if (isset($novosDadosUsuario['tipo'])) {
                $_SESSION['usuario']['tipo'] = $novosDadosUsuario['tipo'];
            }
        }
    }
}
?>