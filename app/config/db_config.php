<?php
function getDatabase()
{
    $host = 'localhost:3306'; // Endereço do servidor de banco de dados
    $username = 'root';  // Usuário do banco de dados
    $password = ''; // Senha do banco de dados
    $dbname = 'ficasuave'; // Nome do banco de dados

    $mysqli = new mysqli($host, $username, $password, $dbname);

    if ($mysqli->connect_error) {
        die("Falha na conexão: " . $mysqli->connect_error);
    }

    return $mysqli;
}
