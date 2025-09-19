<?php
require_once BASE_PATH . '/app/config/db_config.php';

class CartoesModel
{

    private $conn;

    public function __construct()
    {
        $this->conn = getDatabase();
    }
    public function buscarTodas($idUsuario): array
    {
        $idUsuario = (int)$idUsuario;

        $sql = "SELECT c.*, cb.nome_conta 
            FROM cartoes c
            LEFT JOIN contas_bancarias cb ON c.id_conta = cb.id_conta
            WHERE c.id_usuario = $idUsuario
            ORDER BY c.id_cartao ASC";

        $result = $this->conn->query($sql);

        $cartoes = [];
        while ($row = $result->fetch_assoc()) {
            $cartoes[] = $row;
        }

        return $cartoes;
    }
    public function buscarContasDoUsuario($idUsuario): array
    {
        $idUsuario = (int)$idUsuario;

        $sql = "SELECT id_conta, nome_conta 
            FROM contas_bancarias 
            WHERE id_usuario = $idUsuario AND ativa = 1 
            ORDER BY nome_conta";

        $result = $this->conn->query($sql);

        $contas = [];
        while ($row = $result->fetch_assoc()) {
            $contas[] = $row;
        }

        return $contas;
    }
    public function inserir($dados)
    {
        $usuario = $this->conn->real_escape_string($dados['id_usuario']);
        $nome = $this->conn->real_escape_string($dados['nome_cartao']);
        $conta = $this->conn->real_escape_string($dados['id_conta']);
        $tipo = 'credito';
        $bandeira = $this->conn->real_escape_string($dados['bandeira']);

        $diaFechamento = $this->conn->real_escape_string($dados['dia_fechamento']);
        $limite = str_replace(['.', ','], ['', '.'], $dados['limite']);
        $limite = $this->conn->real_escape_string($limite);

        $saldo = isset($dados['saldo_atual']) ? $this->conn->real_escape_string($dados['saldo_atual']) : 0;
        $vencimento = $this->conn->real_escape_string($dados['vencimento_fatura']);

        $sql = "INSERT INTO cartoes (
  id_usuario, nome_cartao, tipo, bandeira, id_conta, limite, saldo_atual, vencimento_fatura, dia_fechamento, data_criacao
) VALUES (
  '$usuario', '$nome', '$tipo', '$bandeira', '$conta', '$limite', '$saldo', '$vencimento', '$diaFechamento', NOW()
)
";



        return $this->conn->query($sql);
    }
    public function calcularFaturaPorVencimento($idCartao, $dataVencimento): float
    {
        $sql = "SELECT SUM(valor) as total
            FROM despesas
            WHERE id_cartao = ?
            AND data_vencimento = ?
            AND status = 'pendente'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $idCartao, $dataVencimento);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return (float) ($row['total'] ?? 0);
    }

    public function calcularFaturaTotalPorVencimento($idCartao, $dataVencimento): float
    {
        $sql = "SELECT SUM(valor) as total
            FROM despesas
            WHERE id_cartao = ?
            AND data_vencimento = ?
            AND status IN ('pendente', 'pago', 'atrasado')";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $idCartao, $dataVencimento);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return (float) ($row['total'] ?? 0);
    }

    public function debugDespesasPorVencimento($idCartao, $dataVencimento): array
    {
        $sql = "SELECT id_despesa, descricao, valor, status, data_vencimento
            FROM despesas
            WHERE id_cartao = ?
            AND data_vencimento = ?
            ORDER BY id_despesa";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $idCartao, $dataVencimento);
        $stmt->execute();
        $result = $stmt->get_result();

        $despesas = [];
        while ($row = $result->fetch_assoc()) {
            $despesas[] = $row;
        }

        return $despesas;
    }

    public function calcularFaturaPorPeriodo($idCartao, $dataInicio, $dataFim): float
    {
        $sql = "SELECT SUM(valor) as total
            FROM despesas
            WHERE id_cartao = ?
            AND DATE(criado_em) >= ?
            AND DATE(criado_em) <= ?
            AND status IN ('pendente', 'pago', 'atrasado')";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iss', $idCartao, $dataInicio, $dataFim);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return (float) ($row['total'] ?? 0);
    }
    public function listarPorUsuario($idUsuario): array
    {
        $idUsuario = (int)$idUsuario;

        $sql = "SELECT c.*, cb.nome_conta 
            FROM cartoes c
            LEFT JOIN contas_bancarias cb ON c.id_conta = cb.id_conta
            WHERE c.id_usuario = ? 
            ORDER BY c.nome_cartao";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $cartoes = [];
        while ($row = $result->fetch_assoc()) {
            $cartoes[] = $row;
        }

        $stmt->close();
        return $cartoes;
    }
    public function calcularGastosPendentesCartao($idCartao): float
    {
        $sql = "SELECT SUM(valor) as total
            FROM despesas
            WHERE id_cartao = $idCartao
              AND status = 'pendente'";

        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return (float) $row['total'] ?? 0;
    }
    public function atualizar($dados)
    {
        $id = (int)$dados['id_cartao'];
        $nome = $this->conn->real_escape_string($dados['nome_cartao']);
        $conta = $this->conn->real_escape_string($dados['id_conta']);
        $tipo = 'credito';
        $bandeira = $this->conn->real_escape_string($dados['bandeira']);

        $limiteStr = preg_replace('/[^\d,\.]/', '', $dados['limite']);
        $limiteFloat = $tipo === 'credito' ? floatval(str_replace(',', '.', str_replace('.', '', $limiteStr))) : 0;
        $limite = $this->conn->real_escape_string($limiteFloat);

        $vencimento = $tipo === 'credito' ? $this->conn->real_escape_string($dados['vencimento_fatura']) : 1;
        $diaFechamento = $tipo === 'credito' ? $this->conn->real_escape_string($dados['dia_fechamento']) : 1;

        $sql = "UPDATE cartoes 
        SET nome_cartao = '$nome',
            tipo = '$tipo',
            id_conta = '$conta',
            limite = '$limite',
            bandeira = '$bandeira',
            vencimento_fatura = '$vencimento',
            dia_fechamento = '$diaFechamento'
        WHERE id_cartao = $id";

        return $this->conn->query($sql);
    }
    public function excluir($id)
    {
        $id = (int)$id;

        return $this->conn->query("DELETE FROM cartoes WHERE id_cartao = $id");
    }

    public function buscarStatusDespesasCartaoMes($idCartao, $dataInicio, $dataFim): string
    {
        $idCartao = (int)$idCartao;
        $dataInicio = $this->conn->real_escape_string($dataInicio);
        $dataFim = $this->conn->real_escape_string($dataFim);

        $sql = "SELECT status, COUNT(*) as quantidade
                FROM despesas 
                WHERE id_cartao = $idCartao 
                AND DATE(data_vencimento) >= '$dataInicio'
                AND DATE(data_vencimento) <= '$dataFim'
                GROUP BY status
                ORDER BY 
                    CASE status 
                        WHEN 'atrasado' THEN 1
                        WHEN 'pendente' THEN 2
                        WHEN 'pago' THEN 3
                    END";

        $result = $this->conn->query($sql);

        // Debug temporário
        error_log("DEBUG Status Despesas - Cartão: $idCartao, Período: $dataInicio a $dataFim, SQL: $sql, Linhas: " . $result->num_rows);

        // Se não há despesas no mês, retorna vazio
        if ($result->num_rows === 0) {
            return '';
        }

        // Pega o primeiro status (prioridade: atrasado > pendente > pago)
        $row = $result->fetch_assoc();
        error_log("DEBUG Status encontrado: " . $row['status']);
        return $row['status'];
    }
}
