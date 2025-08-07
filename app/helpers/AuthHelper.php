<?php

// Detecta se está em produção (ajuste se quiser uma variável de ambiente no futuro)
$isProducao = !in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'ficasuave.test']);

// Define domínio dinamicamente
$cookieDomain = $isProducao ? '.fica-suave.com.br' : '.ficasuave.test';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $cookieDomain,
        'secure' => $isProducao, // true somente em produção real
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start([
        'cookie_lifetime' => 0,
        'cookie_httponly' => true,
        'cookie_secure' => $isProducao, // também true apenas em prod
        'use_strict_mode' => true,
        'use_trans_sid' => false,
        'use_only_cookies' => true,
    ]);
}



function registrarLogin($mensagem, $email = null)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'IP_desconhecido';
    $data = date("Y-m-d H:i:s");
    $linha = "[$data] [$ip] $mensagem - " . ($email ?? 'Email não informado') . "\n";

    $arquivo = BASE_PATH . '/app/helpers/logs/login.log';
    file_put_contents($arquivo, $linha, FILE_APPEND);
}

function verificarLogin()
{
    if (!isset($_SESSION['user_id'])) {
        registrarLogin("Tentativa de acesso sem login");
        header('Location: /login');
        exit();
    }
}

function usuarioLogado()
{
    if (isset($_SESSION['user_id'])) {
        return [
            'id_usuario' => $_SESSION['user_id']
        ];
    }

    return null;
}
