<?php
require_once BASE_PATH . '/app/config/db_config.php';
class BancosModel
{

    private $conn;
    public function __construct()
    {
        $this->conn = getDatabase();
    }

    public function buscarTodos($idUsuario): array
    {

        $idUsuario = (int)$idUsuario;

        $sql = "SELECT * FROM contas_bancarias WHERE id_usuario = $idUsuario ORDER BY id_conta ASC";

        $result = $this->conn->query($sql);

        $bancos = [];
        while ($row = $result->fetch_assoc()) {
            $bancos[] = $row;
        }

        return $bancos;
    }

    public function listarContasPorUsuario($idUsuario): array
    {
        return $this->buscarTodos($idUsuario);
    }


    public function inserir($dados)
    {
        $usuario = (int)$dados['id_usuario'];
        $nome = $this->conn->real_escape_string($dados['nome_conta']);
        $tipo = $this->conn->real_escape_string($dados['tipo']);
        $banco = $this->conn->real_escape_string($dados['banco']);
        $ativa = 1; // Sempre ativa ao criar

        $sql = "INSERT INTO contas_bancarias (id_usuario, nome_conta, tipo, banco, ativa, data_criacao)
            VALUES ('$usuario', '$nome', '$tipo', '$banco', '$ativa', NOW())";

        return $this->conn->query($sql);
    }

    public function atualizar($dados)
    {
        $id = (int)$dados['id_conta'];
        $nome = $this->conn->real_escape_string($dados['nome_conta']);
        $tipo = $this->conn->real_escape_string($dados['tipo']);
        $banco = $this->conn->real_escape_string($dados['banco']);
        $ativa = isset($dados['ativa']) ? 1 : 0;

        $sql = "UPDATE contas_bancarias 
            SET nome_conta = '$nome', tipo = '$tipo', banco = '$banco', ativa = '$ativa'
            WHERE id_conta = $id";

        return $this->conn->query($sql);
    }

    public function excluir($id)
    {
        $id = (int)$id;
        return $this->conn->query("DELETE FROM contas_bancarias WHERE id_conta = $id");
    }
}
