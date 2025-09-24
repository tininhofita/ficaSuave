<?php
function getDatabase()
{
    // Detectar se estamos em produção ou desenvolvimento
    $isProduction = !empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'tininhofita.com') !== false;

    if ($isProduction) {
        // Configurações para PRODUÇÃO (HostGator)
        $host = 'localhost:3306';
        $username = 'tininh93_tininhofita';
        $password = 'Tino7227!';
        $dbname = 'tininh93_ficasuave';
    } else {
        // Configurações para DESENVOLVIMENTO (Local)
        $host = 'localhost:3306';
        $username = 'root';
        $password = '';
        $dbname = 'ficasuave';
    }

    $mysqli = new mysqli($host, $username, $password, $dbname);

    if ($mysqli->connect_error) {
        // Log detalhado do erro
        error_log("Erro de conexão com banco: " . $mysqli->connect_error);
        error_log("Ambiente: " . ($isProduction ? 'PRODUÇÃO' : 'DESENVOLVIMENTO'));
        error_log("Host: $host, User: $username, DB: $dbname");
        die("Falha na conexão com o banco de dados: " . $mysqli->connect_error);
    }

    // Definir charset para UTF-8
    $mysqli->set_charset("utf8");

    return $mysqli;
}
