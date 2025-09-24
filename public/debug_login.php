<?php
// Arquivo temporário para debug do login
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debug do Sistema de Login</h2>";

// Testar sessões
echo "<h3>1. Teste de Sessões:</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color: green;'>✅ Sessão ativa</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Sessão não ativa, iniciando...</p>";
    session_start();
    echo "<p style='color: green;'>✅ Sessão iniciada</p>";
}

echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";

// Testar banco de dados
echo "<h3>2. Teste de Conexão com Banco:</h3>";
try {
    require_once dirname(__DIR__) . '/app/config/db_config.php';
    $mysqli = getDatabase();
    echo "<p style='color: green;'>✅ Conexão com banco OK</p>";
    
    // Testar se existe usuário
    $result = $mysqli->query("SELECT COUNT(*) as total FROM usuarios");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p><strong>Total de usuários no banco:</strong> " . $row['total'] . "</p>";
        
        if ($row['total'] > 0) {
            // Mostrar primeiro usuário (sem senha)
            $result2 = $mysqli->query("SELECT id_usuario, nome_usuario, email FROM usuarios LIMIT 1");
            if ($result2) {
                $user = $result2->fetch_assoc();
                echo "<p><strong>Primeiro usuário:</strong> " . htmlspecialchars($user['nome_usuario']) . " (" . htmlspecialchars($user['email']) . ")</p>";
            }
        }
    }
    
    $mysqli->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no banco: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Testar POST
echo "<h3>3. Dados POST:</h3>";
if ($_POST) {
    echo "<p><strong>Dados recebidos:</strong></p>";
    echo "<pre>" . htmlspecialchars(print_r($_POST, true)) . "</pre>";
} else {
    echo "<p>Nenhum dado POST recebido</p>";
}

// Testar redirecionamento
echo "<h3>4. Teste de Redirecionamento:</h3>";
echo "<p>Se você está logado, deveria ser redirecionado para o dashboard</p>";
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ Usuário logado: " . htmlspecialchars($_SESSION['nome_usuario']) . "</p>";
    echo "<p><a href='/dashboard'>Ir para Dashboard</a></p>";
} else {
    echo "<p style='color: orange;'>⚠️ Usuário não está logado</p>";
}

// Formulário de teste
echo "<h3>5. Formulário de Teste:</h3>";
?>
<form method="POST" action="/login">
    <p>
        <label>Email:</label><br>
        <input type="email" name="email" value="tinofranciel@gmail.com" required>
    </p>
    <p>
        <label>Senha:</label><br>
        <input type="password" name="password" required>
    </p>
    <p>
        <button type="submit">Testar Login</button>
    </p>
</form>

<?php
echo "<hr>";
echo "<p><a href='/login'>Voltar para página de login</a></p>";
?>
