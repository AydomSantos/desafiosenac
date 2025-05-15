<?php
require_once __DIR__ . '/../config.php';

class Sessao {
    public static function iniciar($usuario) {
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
        session_unset();
        session_destroy();
        setcookie('idioma', '', time() - 3600, "/");
        setcookie('tema', '', time() - 3600, "/");
    }

    public static function validar() {
        if (!isset($_SESSION['usuario'])) {
            redirect('login.php');
        }
    }

    public static function getUsuario() {
        return $_SESSION['usuario'] ?? null;
    }

    public static function isAdmin() {
        return isset($_SESSION['usuario']['tipo']) && $_SESSION['usuario']['tipo'] === 'admin';
    }
}
?>