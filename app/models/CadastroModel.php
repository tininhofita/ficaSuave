<?php

class UsuarioModel
{
    private $conn;

    public function __construct()
    {
        require_once BASE_PATH . '/app/config/db_config.php';
        $this->conn = getDatabase();
    }

    public function existeEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();

        return $total > 0;
    }

    public function inserirUsuario($dados)
    {
        $sql = "INSERT INTO usuarios 
        (nome_usuario, email, senha_hash, cep, rua, cidade, estado, uf, pais, renda)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            logEvento("Erro ao preparar o INSERT: " . $this->conn->error, "ERROR");
            return false;
        }

        $stmt->bind_param(
            "ssssssssss",
            $dados['nome'],
            $dados['email'],
            $dados['senha_hash'],
            $dados['cep'],
            $dados['rua'],
            $dados['cidade'],
            $dados['estado'],
            $dados['uf'],
            $dados['pais'],
            $dados['renda']
        );

        if (!$stmt->execute()) {
            logEvento("Erro ao executar INSERT: " . $stmt->error, "ERROR");
            $stmt->close();
            return false;
        }

        $stmt->close();
        return true;
    }
}
