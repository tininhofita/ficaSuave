<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/helpers/logger.php';

try {
    require_once BASE_PATH . '/app/lib/Router.php';
    require_once BASE_PATH . '/app/routers/routes.php';
    $router->dispatch();
} catch (Throwable $e) {
    logEvento("Erro fatal: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
    echo "Erro interno. Tente novamente mais tarde.";
    http_response_code(500);
}
