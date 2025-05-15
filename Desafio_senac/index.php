<?php
require_once 'config.php';

if (estaLogado()) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
?>