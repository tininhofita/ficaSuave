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
        $corCartao = isset($dados['cor_cartao']) ? $this->conn->real_escape_string($dados['cor_cartao']) : '#3b82f6';

        $diaFechamento = $this->conn->real_escape_string($dados['dia_fechamento']);

        // Corrigir formatação do limite
        $limiteStr = preg_replace('/[^\d,\.]/', '', $dados['limite']);
        // Se tem vírgula, é formato brasileiro (1.000,00)
        if (strpos($limiteStr, ',') !== false) {
            $limiteFloat = floatval(str_replace(',', '.', str_replace('.', '', $limiteStr)));
        } else {
            // Se não tem vírgula, é formato americano (1000.00)
            $limiteFloat = floatval($limiteStr);
        }
        $limite = $this->conn->real_escape_string($limiteFloat);

        $saldo = isset($dados['saldo_atual']) ? $this->conn->real_escape_string($dados['saldo_atual']) : 0;
        $vencimento = $this->conn->real_escape_string($dados['vencimento_fatura']);

        $sql = "INSERT INTO cartoes (
  id_usuario, nome_cartao, tipo, bandeira, cor_cartao, id_conta, limite, saldo_atual, vencimento_fatura, dia_fechamento, data_criacao
) VALUES (
  '$usuario', '$nome', '$tipo', '$bandeira', '$corCartao', '$conta', '$limite', '$saldo', '$vencimento', '$diaFechamento', NOW()
)
";

        return $this->conn->query($sql);
    }
    public function calcularFaturaPorVencimento($idCartao, $dataVencimento): float
    {
        $sql = "SELECT SUM(valor) as total
            FROM faturas
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
            FROM faturas
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
        $sql = "SELECT id_fatura, descricao, valor, status, data_vencimento
            FROM faturas
            WHERE id_cartao = ?
            AND data_vencimento = ?
            ORDER BY id_fatura";

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
            FROM faturas
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
            FROM faturas
            WHERE id_cartao = $idCartao
              AND status = 'pendente'";

        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return (float) $row['total'] ?? 0;
    }

    public function calcularGastosPendentesCartaoMes($idCartao, $dataInicio, $dataFim): float
    {
        $idCartao = (int)$idCartao;
        $dataInicio = $this->conn->real_escape_string($dataInicio);
        $dataFim = $this->conn->real_escape_string($dataFim);

        $sql = "SELECT SUM(valor) as total
            FROM faturas
            WHERE id_cartao = $idCartao
              AND status = 'pendente'
              AND DATE(data_vencimento) >= '$dataInicio'
              AND DATE(data_vencimento) <= '$dataFim'";

        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return (float) $row['total'] ?? 0;
    }

    public function calcularGastosNaoPagosCartao($idCartao): float
    {
        $idCartao = (int)$idCartao;

        $sql = "SELECT SUM(valor) as total
            FROM faturas
            WHERE id_cartao = $idCartao
              AND status IN ('pendente', 'atrasado')";

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
        $corCartao = isset($dados['cor_cartao']) ? $this->conn->real_escape_string($dados['cor_cartao']) : '#3b82f6';

        // Corrigir formatação do limite
        $limiteStr = preg_replace('/[^\d,\.]/', '', $dados['limite']);

        // Se tem vírgula, é formato brasileiro (1.000,00)
        if (strpos($limiteStr, ',') !== false) {
            $limiteFloat = floatval(str_replace(',', '.', str_replace('.', '', $limiteStr)));
        } else {
            // Se não tem vírgula, é formato americano (1000.00)
            $limiteFloat = floatval($limiteStr);
        }
        $limite = $this->conn->real_escape_string($limiteFloat);

        $vencimento = $tipo === 'credito' ? $this->conn->real_escape_string($dados['vencimento_fatura']) : 1;
        $diaFechamento = $tipo === 'credito' ? $this->conn->real_escape_string($dados['dia_fechamento']) : 1;

        // Verificar se a coluna cor_cartao existe
        $checkColumn = $this->conn->query("SHOW COLUMNS FROM cartoes LIKE 'cor_cartao'");
        if ($checkColumn->num_rows == 0) {
            // Criar a coluna se não existir
            $this->conn->query("ALTER TABLE cartoes ADD COLUMN cor_cartao VARCHAR(7) DEFAULT '#3b82f6' AFTER bandeira");
        }

        $sql = "UPDATE cartoes 
        SET nome_cartao = '$nome',
            tipo = '$tipo',
            id_conta = '$conta',
            limite = '$limite',
            bandeira = '$bandeira',
            cor_cartao = '$corCartao',
            vencimento_fatura = '$vencimento',
            dia_fechamento = '$diaFechamento'
        WHERE id_cartao = $id";

        $resultado = $this->conn->query($sql);

        return $resultado;
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
                FROM faturas 
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


        // Se não há despesas no mês, retorna vazio
        if ($result->num_rows === 0) {
            return '';
        }

        // Pega o primeiro status (prioridade: atrasado > pendente > pago)
        $row = $result->fetch_assoc();
        return $row['status'];
    }
}
