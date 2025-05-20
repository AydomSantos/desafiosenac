<?php
require_once 'config.php';
require_once 'classes/Sessao.php';

// Encerra a sessão do usuário
Sessao::encerrar();

// Redireciona para a tela de login
redirect('login.php');
?>
