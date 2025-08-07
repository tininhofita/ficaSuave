<?php
require_once BASE_PATH . '/app/config/db_config.php';

class FormaTransacaoModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDatabase();
    }

    public function buscarTodas()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $sql = "SELECT * FROM formas_transacao 
            WHERE padrao = 1 OR id_usuario = $idUsuario 
            ORDER BY id_forma_transacao ASC";

        $result = $this->conn->query($sql);

        $formas = [];
        while ($row = $result->fetch_assoc()) {
            $formas[] = $row;
        }

        return $formas;
    }


    public function inserir($dados)
    {
        $idUsuario = (int)$dados['id_usuario'];
        $nome = $this->conn->real_escape_string($dados['nome']);
        $tipo = $this->conn->real_escape_string($dados['tipo']);
        $uso = $this->conn->real_escape_string($dados['uso']);
        $ativa = (int)$dados['ativa'];

        $sql = "INSERT INTO formas_transacao (id_usuario, nome, tipo, uso, ativa, padrao, data_criacao)
            VALUES ($idUsuario, '$nome', '$tipo', '$uso', $ativa, 0, NOW())";

        return $this->conn->query($sql);
    }


    public function atualizar($dados)
    {
        $id = (int)$dados['id'];
        $nome = $this->conn->real_escape_string($dados['nome']);
        $tipo = $this->conn->real_escape_string($dados['tipo']);
        $uso = $this->conn->real_escape_string($dados['uso']);
        $ativa = (int)$dados['ativa'];

        $sql = "UPDATE formas_transacao 
            SET nome='$nome', tipo='$tipo', uso='$uso', ativa=$ativa 
            WHERE id_forma_transacao = $id";

        return $this->conn->query($sql);
    }

    public function excluir($id)
    {
        $id = (int)$id;

        // Verifica se é padrão
        $verifica = $this->conn->query("SELECT padrao FROM formas_transacao WHERE id_forma_transacao = $id");
        $row = $verifica->fetch_assoc();

        if ($row && $row['padrao']) {
            return false; // impede exclusão de item padrão
        }

        return $this->conn->query("DELETE FROM formas_transacao WHERE id_forma_transacao = $id");
    }
}
