<?php
require_once 'config.php';

// Redireciona com base no estado de autenticação do usuário
if (estaLogado()) {
    redirect('dashboard.php');
}

redirect('login.php');
