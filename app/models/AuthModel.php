<?php

require_once BASE_PATH . '/app/config/db_config.php';
require_once BASE_PATH . '/app/helpers/logger.php';

class AuthModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDatabase(); // Função padrão do db_config.php
    }

    public function buscarPorEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT id_usuario, nome_usuario, senha_hash FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);

        if (!$stmt->execute()) {
            logEvento("Erro ao buscar usuário no banco: " . $stmt->error, 'ERROR');
            return false;
        }

        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            return false;
        }

        return $resultado->fetch_assoc();
    }

    public function registrarLoginBemSucedido($userId)
    {
        $sql = "UPDATE usuarios 
                SET 
                    ultimo_login = NOW(),
                    quantidade_logins = quantidade_logins + 1
                WHERE id_usuario = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    }
}
