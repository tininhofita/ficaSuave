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

    public function realizarTransferencia($contaOrigem, $contaDestino, $valor, $observacao, $idUsuario)
    {
        $contaOrigem = (int)$contaOrigem;
        $contaDestino = (int)$contaDestino;
        $valor = (float)$valor;
        $observacao = $this->conn->real_escape_string($observacao);
        $idUsuario = (int)$idUsuario;

        // Iniciar transação
        $this->conn->begin_transaction();

        try {
            // Verificar se as contas pertencem ao usuário e obter saldos
            $stmt = $this->conn->prepare("
                SELECT id_conta, saldo_atual, nome_conta 
                FROM contas_bancarias 
                WHERE id_conta IN (?, ?) AND id_usuario = ?
            ");
            $stmt->bind_param('iii', $contaOrigem, $contaDestino, $idUsuario);
            $stmt->execute();
            $result = $stmt->get_result();

            $contas = [];
            while ($row = $result->fetch_assoc()) {
                $contas[$row['id_conta']] = $row;
            }
            $stmt->close();

            if (count($contas) !== 2) {
                throw new Exception('Uma ou ambas as contas não foram encontradas ou não pertencem ao usuário');
            }

            $saldoOrigem = (float)$contas[$contaOrigem]['saldo_atual'];
            $nomeOrigem = $contas[$contaOrigem]['nome_conta'];
            $nomeDestino = $contas[$contaDestino]['nome_conta'];

            if ($saldoOrigem < $valor) {
                throw new Exception('Saldo insuficiente na conta de origem');
            }

            // Atualizar saldos
            $stmt1 = $this->conn->prepare("
                UPDATE contas_bancarias 
                SET saldo_atual = saldo_atual - ? 
                WHERE id_conta = ? AND id_usuario = ?
            ");
            $stmt1->bind_param('dii', $valor, $contaOrigem, $idUsuario);
            $stmt1->execute();
            $stmt1->close();

            $stmt2 = $this->conn->prepare("
                UPDATE contas_bancarias 
                SET saldo_atual = saldo_atual + ? 
                WHERE id_conta = ? AND id_usuario = ?
            ");
            $stmt2->bind_param('dii', $valor, $contaDestino, $idUsuario);
            $stmt2->execute();
            $stmt2->close();

            // Registrar histórico da transferência (se existir tabela de movimentações)
            $descricao = "Transferência de {$nomeOrigem} para {$nomeDestino}";
            if (!empty($observacao)) {
                $descricao .= " - {$observacao}";
            }

            // Aqui você pode inserir em uma tabela de histórico/movimentações se existir
            // Por enquanto, vamos apenas registrar no log
            error_log("Transferência realizada: {$valor} de conta {$contaOrigem} para {$contaDestino} - {$descricao}");

            $this->conn->commit();
            return ['sucesso' => true, 'mensagem' => 'Transferência realizada com sucesso'];
        } catch (\Throwable $e) {
            $this->conn->rollback();
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
}
