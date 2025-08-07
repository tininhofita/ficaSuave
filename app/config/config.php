<?php
require_once BASE_PATH . '/app/helpers/AuthHelper.php';
require_once __DIR__ . '/db_config.php';

// Timeout
$tempo_max_inativo = 3600;
if (isset($_SESSION['ultimo_acesso'])) {
    if (time() - $_SESSION['ultimo_acesso'] > $tempo_max_inativo) {
        session_unset();
        session_destroy();
        header('Location: /login');
        exit();
    }
}
$_SESSION['ultimo_acesso'] = time();

// Verifica se logado
verificarLogin();
