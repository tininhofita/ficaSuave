<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));

// Detectar ambiente
$isProduction = !empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'tininhofita.com') !== false;

try {
    require_once BASE_PATH . '/app/helpers/logger.php';
    require_once BASE_PATH . '/app/lib/Router.php';
    require_once BASE_PATH . '/app/routers/routes.php';
    $router->dispatch();
} catch (Throwable $e) {
    // Log detalhado do erro
    $errorMsg = "Erro fatal: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine();
    error_log($errorMsg);
    
    // Tentar logar no sistema se possível
    if (function_exists('logEvento')) {
        logEvento($errorMsg);
    }
    
    // Definir código de resposta antes de qualquer output
    if (!headers_sent()) {
        http_response_code(500);
    }
    
    // Mostrar erro detalhado em desenvolvimento, genérico em produção
    if (!$isProduction) {
        echo "<h2>Erro de Desenvolvimento:</h2>";
        echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
        echo "<p><strong>Stack Trace:</strong></p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "Erro interno. Tente novamente mais tarde.";
    }
}
