<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/config/db_config.php';

$mysqli = getDatabase();

if ($mysqli->connect_error) {
    echo '❌ Erro na conexão: ' . $mysqli->connect_error;
} else {
    echo '✅ Conexão com o banco de dados foi bem-sucedida!';
}
