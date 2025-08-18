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
        $nome    = $this->conn->real_escape_string($dados['nome_conta']);
        $tipo    = $this->conn->real_escape_string($dados['tipo']);
        $banco   = $this->conn->real_escape_string($dados['banco'] ?? '');
        $ativa   = !empty($dados['ativa']) ? 1 : 0;

        // Normalização robusta do saldo_inicial (aceita "1.234,56", "10,00" ou "10.00")
        $saldoStr = trim((string)($dados['saldo_inicial'] ?? '0'));
        if ($saldoStr === '') $saldoStr = '0';

        if (strpos($saldoStr, ',') !== false && strpos($saldoStr, '.') !== false) {
            // caso "1.234,56" -> remove '.' de milhar e troca ',' por '.'
            $saldoStr = str_replace('.', '', $saldoStr);
            $saldoStr = str_replace(',', '.', $saldoStr);
        } elseif (strpos($saldoStr, ',') !== false) {
            // caso "10,00" -> troca ',' por '.'
            $saldoStr = str_replace(',', '.', $saldoStr);
        }
        // caso "10.00" -> já está ok

        $saldoInicial = (float)$saldoStr;

        $sql = "
        INSERT INTO contas_bancarias
            (id_usuario, nome_conta, tipo, banco, saldo_inicial, saldo_atual, ativa, data_criacao)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, NOW())
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            'isssddi',
            $usuario,
            $nome,
            $tipo,
            $banco,
            $saldoInicial,
            $saldoInicial, // saldo_atual inicia igual ao inicial
            $ativa
        );

        return $stmt->execute();
    }

    public function definirFavoritaToggle(int $idConta, int $idUsuario): bool
    {
        // verifica se a conta é do usuário e o estado atual
        $stmt = $this->conn->prepare("
        SELECT favorita
        FROM contas_bancarias
        WHERE id_conta = ? AND id_usuario = ?
        LIMIT 1
    ");
        $stmt->bind_param('ii', $idConta, $idUsuario);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) return false;
        $jaFav = (int)$row['favorita'] === 1;

        $this->conn->begin_transaction();
        try {
            if ($jaFav) {
                // desfavorita a atual → nenhuma favorita
                $stmt1 = $this->conn->prepare("
                UPDATE contas_bancarias SET favorita = 0
                WHERE id_conta = ? AND id_usuario = ?
            ");
                $stmt1->bind_param('ii', $idConta, $idUsuario);
                $stmt1->execute();
                $stmt1->close();
            } else {
                // zera todas e marca a escolhida
                $stmt1 = $this->conn->prepare("
                UPDATE contas_bancarias SET favorita = 0
                WHERE id_usuario = ?
            ");
                $stmt1->bind_param('i', $idUsuario);
                $stmt1->execute();
                $stmt1->close();

                $stmt2 = $this->conn->prepare("
                UPDATE contas_bancarias SET favorita = 1
                WHERE id_conta = ? AND id_usuario = ?
            ");
                $stmt2->bind_param('ii', $idConta, $idUsuario);
                $stmt2->execute();
                $stmt2->close();
            }

            $this->conn->commit();
            return true;
        } catch (\Throwable $e) {
            $this->conn->rollback();
            return false;
        }
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
