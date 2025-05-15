<?php
require_once 'config.php';
require_once 'classes/Sessao.php';

Sessao::encerrar();
redirect('login.php');
?>