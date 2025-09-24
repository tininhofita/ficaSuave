<?php
function getDatabase()
{
    // Configurações para PRODUÇÃO (HostGator)
    $host = 'localhost';
    $username = 'tininh93_tininhofita';
    $password = 'Tino7227!7804@';
    $dbname = 'tininh93_ficasuave';

    $mysqli = new mysqli($host, $username, $password, $dbname);

    if ($mysqli->connect_error) {
        // Log detalhado do erro
        error_log("Erro de conexão com banco: " . $mysqli->connect_error);
        error_log("Host: $host, User: $username, DB: $dbname");
        die("Falha na conexão com o banco de dados: " . $mysqli->connect_error);
    }

    // Definir charset para UTF-8
    $mysqli->set_charset("utf8");

    return $mysqli;
}
