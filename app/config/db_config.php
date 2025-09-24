<?php
function getDatabase()
{
    // Detectar se estamos em produção ou desenvolvimento
    $isProduction = !empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'tininhofita.com') !== false;

    if ($isProduction) {
        // Configurações para PRODUÇÃO (HostGator)
        $host = 'localhost:3306';
        $username = 'tininh93_tininhofita';  // Ajustar conforme necessário
        $password = 'Tino7227!7804@'; // Ajustar conforme necessário
        $dbname = 'tininh93_ficasuave';   // Ajustar conforme necessário
    } else {
        // Configurações para DESENVOLVIMENTO (Local)
        $host = 'localhost:3306';
        $username = 'root';
        $password = '';
        $dbname = 'ficasuave';
    }

    $mysqli = new mysqli($host, $username, $password, $dbname);

    if ($mysqli->connect_error) {
        // Log do erro para debug
        error_log("Erro de conexão com banco: " . $mysqli->connect_error . " (Ambiente: " . ($isProduction ? 'PRODUÇÃO' : 'DESENVOLVIMENTO') . ")");
        die("Falha na conexão com o banco de dados. Verifique as configurações.");
    }

    // Definir charset para UTF-8
    $mysqli->set_charset("utf8");

    return $mysqli;
}
