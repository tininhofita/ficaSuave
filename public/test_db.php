<?php
// Arquivo temporário para testar conexão com banco
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Teste de Conexão com Banco de Dados</h2>";

// Detectar ambiente
$isProduction = !empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'tininhofita.com') !== false;
echo "<p><strong>Ambiente:</strong> " . ($isProduction ? 'PRODUÇÃO' : 'DESENVOLVIMENTO') . "</p>";
echo "<p><strong>Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";

try {
    require_once dirname(__DIR__) . '/app/config/db_config.php';
    
    echo "<p>Tentando conectar com o banco...</p>";
    
    $mysqli = getDatabase();
    
    echo "<p style='color: green;'><strong>✅ Conexão com banco bem-sucedida!</strong></p>";
    echo "<p><strong>Versão MySQL:</strong> " . $mysqli->server_info . "</p>";
    echo "<p><strong>Charset:</strong> " . $mysqli->character_set_name() . "</p>";
    
    // Testar uma query simples
    $result = $mysqli->query("SELECT 1 as test");
    if ($result) {
        echo "<p style='color: green;'><strong>✅ Query de teste executada com sucesso!</strong></p>";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Erro na conexão:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'><strong>❌ Erro fatal:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
}
?>
