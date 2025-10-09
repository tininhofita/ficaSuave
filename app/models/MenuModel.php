<?php
require_once BASE_PATH . '/app/config/db_config.php';

class MenuModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDatabase();
    }

    public function somarDespesasCartao($idUsuario, $mes = null, $ano = null): float
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT SUM(valor) as total 
        FROM faturas 
        WHERE id_usuario = ? 
        AND MONTH(data_vencimento) = ?
        AND YEAR(data_vencimento) = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (float) ($result['total'] ?? 0);
    }

    public function somarDespesas($idUsuario, $mes = null, $ano = null): float
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT SUM(valor) as total 
        FROM despesas 
        WHERE id_usuario = ? 
        AND MONTH(data_vencimento) = ?
        AND YEAR(data_vencimento) = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (float) ($result['total'] ?? 0);
    }

    public function somarDespesasPendentes($idUsuario, $mes = null, $ano = null): float
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT SUM(valor) as total 
        FROM despesas 
        WHERE id_usuario = ? 
        AND status = 'pendente'
        AND MONTH(data_vencimento) = ?
        AND YEAR(data_vencimento) = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (float) ($result['total'] ?? 0);
    }

    public function somarDespesasPagas($idUsuario, $mes = null, $ano = null): float
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT SUM(valor_pago) as total 
        FROM despesas 
        WHERE id_usuario = ? 
        AND status = 'pago'
        AND MONTH(data_pagamento) = ?
        AND YEAR(data_pagamento) = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (float) ($result['total'] ?? 0);
    }

    // Pega o saldo atual da conta marcada como favorita
    public function getSaldoContaFavorita(int $idUsuario): float
    {
        $sql = "SELECT saldo_atual
            FROM contas_bancarias
            WHERE id_usuario = ?
              AND favorita = 1
            LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (float) ($row['saldo_atual'] ?? 0.00);
    }


    public function listarGastosPorCartao($idUsuario, $mes = null, $ano = null): array
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT c.id_cartao, c.nome_cartao, SUM(f.valor) as total 
        FROM faturas f
        INNER JOIN cartoes c ON f.id_cartao = c.id_cartao
        WHERE f.id_usuario = ?
        AND MONTH(f.data_vencimento) = ?
        AND YEAR(f.data_vencimento) = ?
        GROUP BY c.id_cartao, c.nome_cartao
        ORDER BY total DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result();
        $dados = [];

        $totalGeral = 0;
        while ($row = $result->fetch_assoc()) {
            $totalGeral += (float) $row['total'];
            $dados[] = $row;
        }

        // calcula porcentagens
        foreach ($dados as &$linha) {
            $linha['percentual'] = $totalGeral > 0 ? round(($linha['total'] / $totalGeral) * 100, 2) : 0;
        }

        return $dados;
    }

    // Categorias por cartão para tooltip
    public function buscarCategoriasPorCartao($idUsuario, $idCartao, $mes = null, $ano = null): array
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT c.id_categoria, c.nome_categoria, SUM(f.valor) as total 
        FROM faturas f
        INNER JOIN categorias c ON f.id_categoria = c.id_categoria
        WHERE f.id_usuario = ?
        AND f.id_cartao = ?
        AND MONTH(f.data_vencimento) = ?
        AND YEAR(f.data_vencimento) = ?
        GROUP BY c.id_categoria, c.nome_categoria
        ORDER BY total DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iiii', $idUsuario, $idCartao, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result();
        $dados = [];

        $totalGeral = 0;
        while ($row = $result->fetch_assoc()) {
            $totalGeral += (float) $row['total'];
            $dados[] = $row;
        }

        // calcula porcentagens
        foreach ($dados as &$linha) {
            $linha['percentual'] = $totalGeral > 0 ? round(($linha['total'] / $totalGeral) * 100, 2) : 0;
        }
        return $dados;
    }

    public function listarGastosPorCategoria($idUsuario, $mes = null, $ano = null): array
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT 
                d.id_categoria,
                c.nome_categoria,
                SUM(d.valor) AS total
            FROM despesas d
            INNER JOIN categorias c ON d.id_categoria = c.id_categoria
            WHERE d.id_usuario = ?
              AND MONTH(d.data_vencimento) = ?
              AND YEAR(d.data_vencimento) = ?
            GROUP BY d.id_categoria, c.nome_categoria
            ORDER BY total DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result();
        $dados = [];
        $totalGeral = 0;

        while ($row = $result->fetch_assoc()) {
            $totalGeral += (float) $row['total'];
            $dados[] = $row;
        }

        foreach ($dados as &$linha) {
            $linha['percentual'] = $totalGeral > 0
                ? round(($linha['total'] / $totalGeral) * 100, 2)
                : 0;
        }

        return $dados;
    }


    public function buscarDespesasPorSubcategoria($idUsuario, $idCategoria, $mes = null, $ano = null): array
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT s.nome_subcategoria, SUM(d.valor) as total 
        FROM despesas d
        INNER JOIN subcategorias s ON d.id_subcategoria = s.id_subcategoria
        WHERE d.id_usuario = ?
        AND d.id_categoria = ?
        AND MONTH(data_vencimento) = ?
        AND YEAR(data_vencimento) = ?
        GROUP BY s.nome_subcategoria
        ORDER BY total DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iiii', $idUsuario, $idCategoria, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result();
        $dados = [];

        $totalGeral = 0;
        while ($row = $result->fetch_assoc()) {
            $totalGeral += (float) $row['total'];
            $dados[] = $row;
        }
        // calcula porcentagens
        foreach ($dados as &$linha) {
            $linha['percentual'] = $totalGeral > 0 ? round(($linha['total'] / $totalGeral) * 100, 2) : 0;
        }
        return $dados;
    }


    // Receitas

    public function getTotalReceitasMes(int $idUsuario, $mes = null, $ano = null): float
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "
        SELECT COALESCE(SUM(valor),0) AS total
        FROM receitas
        WHERE id_usuario = ?
          AND MONTH(data_vencimento) = ?
          AND YEAR(data_vencimento) = ?
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (float) $row['total'];
    }

    public function listarReceitasPorCategoria($idUsuario, $mes = null, $ano = null): array
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT c.nome_categoria, SUM(r.valor) as total 
        FROM receitas r
        INNER JOIN categorias c ON r.id_categoria = c.id_categoria
        WHERE r.id_usuario = ?
        AND MONTH(data_vencimento) = ?
        AND YEAR(data_vencimento) = ?
        GROUP BY c.nome_categoria
        ORDER BY total DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result();
        $dados = [];

        $totalGeral = 0;
        while ($row = $result->fetch_assoc()) {
            $totalGeral += (float) $row['total'];
            $dados[] = $row;
        }

        // calcula porcentagens
        foreach ($dados as &$linha) {
            $linha['percentual'] = $totalGeral > 0 ? round(($linha['total'] / $totalGeral) * 100, 2) : 0;
        }

        return $dados;
    }


    // Total de receitas já recebidas
    public function getTotalReceitasRecebidas(int $idUsuario, $mes = null, $ano = null): float
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "
        SELECT COALESCE(SUM(valor_recebido),0) AS total
        FROM receitas 
        WHERE id_usuario = ? 
            AND status = 'recebido'
            AND MONTH(data_vencimento) = ?
            AND YEAR(data_vencimento) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();
        return (float) $stmt->get_result()->fetch_assoc()['total'];
    }

    // Total de receitas pendentes (previstas)
    public function getTotalReceitasPendentes(int $idUsuario, $mes = null, $ano = null): float
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "
        SELECT COALESCE(SUM(valor),0) AS total
        FROM receitas 
        WHERE id_usuario = ? 
            AND status = 'previsto'
            AND MONTH(data_vencimento) = ?
            AND YEAR(data_vencimento) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();
        return (float) $stmt->get_result()->fetch_assoc()['total'];
    }

    // app/models/MenuModel.php

    public function getReceitasPorMes(int $idUsuario): array
    {
        $sql = "
      SELECT MONTH(data_vencimento) AS mes, COALESCE(SUM(valor),0) AS total
      FROM receitas
      WHERE id_usuario = ?
        AND YEAR(data_vencimento) = YEAR(CURRENT_DATE())
      GROUP BY mes
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();

        // cria array com 12 zeros
        $totais = array_fill(0, 12, 0);
        while ($row = $res->fetch_assoc()) {
            $totais[$row['mes'] - 1] = (float) $row['total'];
        }
        return $totais;
    }

    public function getDespesasPorMes(int $idUsuario): array
    {
        $sql = "
      SELECT MONTH(data_vencimento) AS mes, COALESCE(SUM(valor),0) AS total
      FROM despesas
      WHERE id_usuario = ?
        AND YEAR(data_vencimento) = YEAR(CURRENT_DATE())
      GROUP BY mes
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();

        $totais = array_fill(0, 12, 0);
        while ($row = $res->fetch_assoc()) {
            $totais[$row['mes'] - 1] = (float) $row['total'];
        }
        return $totais;
    }

    // Gastos por categoria da tabela faturas
    public function listarGastosPorCategoriaFatura($idUsuario, $mes = null, $ano = null): array
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT 
                f.id_categoria,
                c.nome_categoria,
                SUM(f.valor) AS total
            FROM faturas f
            INNER JOIN categorias c ON f.id_categoria = c.id_categoria
            WHERE f.id_usuario = ?
              AND MONTH(f.data_vencimento) = ?
              AND YEAR(f.data_vencimento) = ?
            GROUP BY f.id_categoria, c.nome_categoria
            ORDER BY total DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result();
        $dados = [];
        $totalGeral = 0;

        while ($row = $result->fetch_assoc()) {
            $totalGeral += (float) $row['total'];
            $dados[] = $row;
        }

        foreach ($dados as &$linha) {
            $linha['percentual'] = $totalGeral > 0
                ? round(($linha['total'] / $totalGeral) * 100, 2)
                : 0;
        }

        return $dados;
    }

    // Subcategorias das faturas por categoria
    public function buscarSubcategoriasPorCategoriaFatura($idUsuario, $idCategoria, $mes = null, $ano = null): array
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT s.nome_subcategoria, SUM(f.valor) as total 
        FROM faturas f
        INNER JOIN subcategorias s ON f.id_subcategoria = s.id_subcategoria
        WHERE f.id_usuario = ?
        AND f.id_categoria = ?
        AND MONTH(data_vencimento) = ?
        AND YEAR(data_vencimento) = ?
        GROUP BY s.nome_subcategoria
        ORDER BY total DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iiii', $idUsuario, $idCategoria, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result();
        $dados = [];

        $totalGeral = 0;
        while ($row = $result->fetch_assoc()) {
            $totalGeral += (float) $row['total'];
            $dados[] = $row;
        }

        // calcula porcentagens
        foreach ($dados as &$linha) {
            $linha['percentual'] = $totalGeral > 0 ? round(($linha['total'] / $totalGeral) * 100, 2) : 0;
        }
        return $dados;
    }

    // Total de despesas de cartão pendentes
    public function somarFaturasPendentes($idUsuario, $mes = null, $ano = null): float
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT SUM(valor) as total 
        FROM faturas 
        WHERE id_usuario = ? 
        AND status = 'pendente'
        AND MONTH(data_vencimento) = ?
        AND YEAR(data_vencimento) = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (float) ($result['total'] ?? 0);
    }

    // ===== MÉTRICAS DE TENDÊNCIA =====

    // Receitas do mês anterior para comparação
    public function getReceitasMesAnterior($idUsuario, $mes = null, $ano = null): float
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        // Calcula mês anterior
        $mesAnterior = $mes - 1;
        $anoAnterior = $ano;
        if ($mesAnterior <= 0) {
            $mesAnterior = 12;
            $anoAnterior = $ano - 1;
        }

        $sql = "SELECT COALESCE(SUM(valor_recebido), 0) AS total
        FROM receitas 
        WHERE id_usuario = ? 
            AND status = 'recebido'
            AND MONTH(data_vencimento) = ?
            AND YEAR(data_vencimento) = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mesAnterior, $anoAnterior);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (float) ($result['total'] ?? 0);
    }

    // Despesas pagas do mês anterior para comparação
    public function getDespesasPagasMesAnterior($idUsuario, $mes = null, $ano = null): float
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        // Calcula mês anterior
        $mesAnterior = $mes - 1;
        $anoAnterior = $ano;
        if ($mesAnterior <= 0) {
            $mesAnterior = 12;
            $anoAnterior = $ano - 1;
        }

        $sql = "SELECT COALESCE(SUM(valor_pago), 0) AS total
        FROM despesas 
        WHERE id_usuario = ? 
            AND status = 'pago'
            AND MONTH(data_pagamento) = ?
            AND YEAR(data_pagamento) = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mesAnterior, $anoAnterior);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (float) ($result['total'] ?? 0);
    }

    // Gastos em cartão do mês anterior
    public function getGastosCartaoMesAnterior($idUsuario, $mes = null, $ano = null): float
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        // Calcula mês anterior
        $mesAnterior = $mes - 1;
        $anoAnterior = $ano;
        if ($mesAnterior <= 0) {
            $mesAnterior = 12;
            $anoAnterior = $ano - 1;
        }

        $sql = "SELECT COALESCE(SUM(valor), 0) AS total
        FROM faturas 
        WHERE id_usuario = ? 
            AND MONTH(data_vencimento) = ?
            AND YEAR(data_vencimento) = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mesAnterior, $anoAnterior);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (float) ($result['total'] ?? 0);
    }

    // ===== KPIs FINANCEIROS =====

    // Taxa de poupança (receitas - despesas) / receitas * 100
    public function getTaxaPoupanca($idUsuario, $mes = null, $ano = null): float
    {
        $receitas = $this->getTotalReceitasRecebidas($idUsuario, $mes, $ano);
        $despesas = $this->somarDespesasPagas($idUsuario, $mes, $ano);

        if ($receitas <= 0) return 0;

        return round((($receitas - $despesas) / $receitas) * 100, 2);
    }

    // Endividamento (cartões pendentes / receitas mensais) * 100
    public function getTaxaEndividamento($idUsuario, $mes = null, $ano = null): float
    {
        $faturasPendentes = $this->somarFaturasPendentes($idUsuario, $mes, $ano);
        $receitas = $this->getTotalReceitasRecebidas($idUsuario, $mes, $ano);

        if ($receitas <= 0) return 0;

        return round(($faturasPendentes / $receitas) * 100, 2);
    }

    // Liquidez (saldo disponível / despesas mensais) 
    public function getLiquidez($idUsuario, $mes = null, $ano = null): float
    {
        $saldo = $this->getSaldoContaFavorita($idUsuario);
        $despesas = $this->somarDespesasPagas($idUsuario, $mes, $ano);

        if ($despesas <= 0) return 0;

        return round($saldo / $despesas, 2);
    }

    // Eficiência de pagamentos (despesas pagas / despesas totais) * 100
    public function getEficienciaPagamentos($idUsuario, $mes = null, $ano = null): float
    {
        $despesasPagas = $this->somarDespesasPagas($idUsuario, $mes, $ano);
        $despesasTotais = $this->somarDespesas($idUsuario, $mes, $ano);

        if ($despesasTotais <= 0) return 0;

        return round(($despesasPagas / $despesasTotais) * 100, 2);
    }

    // ===== ANÁLISE AVANÇADA DE CARTÕES =====

    // Lista cartões com limites e utilização
    public function getCartoesComLimites($idUsuario): array
    {
        $sql = "SELECT 
                    c.id_cartao,
                    c.nome_cartao,
                    c.tipo,
                    c.bandeira,
                    c.cor_cartao,
                    c.limite,
                    c.saldo_atual,
                    c.vencimento_fatura,
                    c.dia_fechamento,
                    COALESCE(SUM(f.valor), 0) as gasto_mes
                FROM cartoes c
                LEFT JOIN faturas f ON c.id_cartao = f.id_cartao 
                    AND MONTH(f.data_vencimento) = MONTH(CURRENT_DATE())
                    AND YEAR(f.data_vencimento) = YEAR(CURRENT_DATE())
                WHERE c.id_usuario = ?
                GROUP BY c.id_cartao
                ORDER BY gasto_mes DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();

        $result = $stmt->get_result();
        $cartoes = [];

        while ($row = $result->fetch_assoc()) {
            $limite = (float) $row['limite'];
            $gasto = (float) $row['gasto_mes'];
            $saldoAtual = (float) $row['saldo_atual'];

            // Calcula métricas de utilização
            $utilizacao = $limite > 0 ? round(($gasto / $limite) * 100, 2) : 0;
            $disponivel = $limite - $gasto;
            $status = $utilizacao >= 90 ? 'critico' : ($utilizacao >= 70 ? 'atencao' : 'normal');

            $row['utilizacao_percentual'] = $utilizacao;
            $row['limite_disponivel'] = $disponivel;
            $row['status'] = $status;
            $row['saldo_atual'] = $saldoAtual;

            $cartoes[] = $row;
        }

        return $cartoes;
    }

    // Cartão mais usado no mês
    public function getCartaoMaisUsado($idUsuario, $mes = null, $ano = null): array
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT 
                    c.nome_cartao,
                    c.bandeira,
                    SUM(f.valor) as total_gasto,
                    COUNT(f.id_fatura) as qtd_transacoes
                FROM cartoes c
                INNER JOIN faturas f ON c.id_cartao = f.id_cartao
                WHERE c.id_usuario = ?
                    AND MONTH(f.data_vencimento) = ?
                    AND YEAR(f.data_vencimento) = ?
                GROUP BY c.id_cartao
                ORDER BY total_gasto DESC
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $idUsuario, $mes, $ano);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: [];
    }

    // Cartões próximos do limite
    public function getCartoesProximosLimite($idUsuario, $percentualMinimo = 70): array
    {
        $sql = "SELECT 
                    c.nome_cartao,
                    c.limite,
                    c.saldo_atual,
                    COALESCE(SUM(f.valor), 0) as gasto_mes,
                    ROUND((COALESCE(SUM(f.valor), 0) / c.limite) * 100, 2) as utilizacao
                FROM cartoes c
                LEFT JOIN faturas f ON c.id_cartao = f.id_cartao 
                    AND MONTH(f.data_vencimento) = MONTH(CURRENT_DATE())
                    AND YEAR(f.data_vencimento) = YEAR(CURRENT_DATE())
                WHERE c.id_usuario = ?
                    AND c.limite > 0
                GROUP BY c.id_cartao
                HAVING utilizacao >= ?
                ORDER BY utilizacao DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('id', $idUsuario, $percentualMinimo);
        $stmt->execute();

        $result = $stmt->get_result();
        $cartoes = [];

        while ($row = $result->fetch_assoc()) {
            $cartoes[] = $row;
        }

        return $cartoes;
    }

    // Análise de bandeiras de cartão
    public function getAnaliseBandeiras($idUsuario, $mes = null, $ano = null): array
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $sql = "SELECT 
                    c.bandeira,
                    COUNT(DISTINCT c.id_cartao) as qtd_cartoes,
                    COALESCE(SUM(f.valor), 0) as total_gasto,
                    COALESCE(AVG(f.valor), 0) as ticket_medio,
                    COUNT(f.id_fatura) as qtd_transacoes
                FROM cartoes c
                LEFT JOIN faturas f ON c.id_cartao = f.id_cartao
                    AND MONTH(f.data_vencimento) = ?
                    AND YEAR(f.data_vencimento) = ?
                WHERE c.id_usuario = ?
                GROUP BY c.bandeira
                ORDER BY total_gasto DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $mes, $ano, $idUsuario);
        $stmt->execute();

        $result = $stmt->get_result();
        $bandeiras = [];
        $totalGeral = 0;

        while ($row = $result->fetch_assoc()) {
            $totalGeral += (float) $row['total_gasto'];
            $bandeiras[] = $row;
        }

        // Calcula percentuais
        foreach ($bandeiras as &$bandeira) {
            $bandeira['percentual'] = $totalGeral > 0
                ? round(($bandeira['total_gasto'] / $totalGeral) * 100, 2)
                : 0;
        }

        return $bandeiras;
    }

    // Histórico de gastos por cartão (últimos 6 meses)
    public function getHistoricoGastosCartao($idUsuario, $idCartao = null): array
    {
        $sql = "SELECT 
                    c.nome_cartao,
                    MONTH(f.data_vencimento) as mes,
                    YEAR(f.data_vencimento) as ano,
                    COALESCE(SUM(f.valor), 0) as total
                FROM cartoes c
                LEFT JOIN faturas f ON c.id_cartao = f.id_cartao
                    AND f.data_vencimento >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                WHERE c.id_usuario = ?";

        if ($idCartao) {
            $sql .= " AND c.id_cartao = ?";
        }

        $sql .= " GROUP BY c.id_cartao, MONTH(f.data_vencimento), YEAR(f.data_vencimento)
                  ORDER BY c.nome_cartao, ano DESC, mes DESC";

        $stmt = $this->conn->prepare($sql);
        if ($idCartao) {
            $stmt->bind_param('ii', $idUsuario, $idCartao);
        } else {
            $stmt->bind_param('i', $idUsuario);
        }
        $stmt->execute();

        $result = $stmt->get_result();
        $historico = [];

        while ($row = $result->fetch_assoc()) {
            $historico[] = $row;
        }

        return $historico;
    }

    // ===== SISTEMA DE ALERTAS E RECOMENDAÇÕES =====

    // Gera alertas financeiros baseados nos dados do usuário
    public function getAlertasFinanceiros($idUsuario, $mes = null, $ano = null): array
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $alertas = [];

        // Dados para análise
        $receitas = $this->getTotalReceitasRecebidas($idUsuario, $mes, $ano);
        $despesas = $this->somarDespesasPagas($idUsuario, $mes, $ano);
        $saldo = $this->getSaldoContaFavorita($idUsuario);
        $taxaPoupanca = $this->getTaxaPoupanca($idUsuario, $mes, $ano);
        $endividamento = $this->getTaxaEndividamento($idUsuario, $mes, $ano);
        $cartoesProximosLimite = $this->getCartoesProximosLimite($idUsuario);

        // 1. Alertas de Saldo Negativo
        if ($saldo < 0) {
            $alertas[] = [
                'tipo' => 'critico',
                'titulo' => 'Saldo Negativo',
                'mensagem' => 'Sua conta está com saldo negativo de R$ ' . number_format(abs($saldo), 2, ',', '.'),
                'acao' => 'Deposite dinheiro urgentemente para evitar juros',
                'icone' => 'ph-warning-circle'
            ];
        }

        // 2. Alertas de Taxa de Poupança Baixa
        if ($taxaPoupanca < 0) {
            $alertas[] = [
                'tipo' => 'critico',
                'titulo' => 'Gastos Acima da Renda',
                'mensagem' => 'Você está gastando R$ ' . number_format(abs($despesas - $receitas), 2, ',', '.') . ' a mais do que recebe',
                'acao' => 'Reduza gastos ou aumente receitas imediatamente',
                'icone' => 'ph-trend-down'
            ];
        } elseif ($taxaPoupanca < 10 && $taxaPoupanca >= 0) {
            $alertas[] = [
                'tipo' => 'atencao',
                'titulo' => 'Poupança Baixa',
                'mensagem' => 'Você está poupando apenas ' . number_format($taxaPoupanca, 1) . '% da renda',
                'acao' => 'Tente aumentar para pelo menos 10% do seu salário',
                'icone' => 'ph-piggy-bank'
            ];
        }

        // 3. Alertas de Endividamento Alto
        if ($endividamento > 50) {
            $alertas[] = [
                'tipo' => 'critico',
                'titulo' => 'Endividamento Crítico',
                'mensagem' => 'Cartões representam ' . number_format($endividamento, 1) . '% da sua renda',
                'acao' => 'Priorize o pagamento das faturas de cartão',
                'icone' => 'ph-credit-card'
            ];
        } elseif ($endividamento > 30) {
            $alertas[] = [
                'tipo' => 'atencao',
                'titulo' => 'Endividamento Elevado',
                'mensagem' => 'Cartões representam ' . number_format($endividamento, 1) . '% da sua renda',
                'acao' => 'Considere reduzir gastos com cartão',
                'icone' => 'ph-credit-card'
            ];
        }

        // 4. Alertas de Cartões Próximos do Limite
        if (!empty($cartoesProximosLimite)) {
            foreach ($cartoesProximosLimite as $cartao) {
                if ($cartao['utilizacao'] >= 90) {
                    $alertas[] = [
                        'tipo' => 'critico',
                        'titulo' => 'Cartão no Limite',
                        'mensagem' => $cartao['nome_cartao'] . ' está ' . number_format($cartao['utilizacao'], 1) . '% utilizado',
                        'acao' => 'Evite usar este cartão até pagar a fatura',
                        'icone' => 'ph-credit-card'
                    ];
                } else {
                    $alertas[] = [
                        'tipo' => 'atencao',
                        'titulo' => 'Cartão em Alerta',
                        'mensagem' => $cartao['nome_cartao'] . ' está ' . number_format($cartao['utilizacao'], 1) . '% utilizado',
                        'acao' => 'Cuidado para não ultrapassar o limite',
                        'icone' => 'ph-warning'
                    ];
                }
            }
        }

        // 5. Alertas de Liquidez Baixa
        $liquidez = $this->getLiquidez($idUsuario, $mes, $ano);
        if ($liquidez < 1) {
            $alertas[] = [
                'tipo' => 'critico',
                'titulo' => 'Reserva de Emergência Baixa',
                'mensagem' => 'Você tem reserva para apenas ' . number_format($liquidez, 1) . ' mês(es) de gastos',
                'acao' => 'Construa uma reserva de pelo menos 3 meses de gastos',
                'icone' => 'ph-coins'
            ];
        } elseif ($liquidez < 3) {
            $alertas[] = [
                'tipo' => 'atencao',
                'titulo' => 'Reserva de Emergência Pequena',
                'mensagem' => 'Você tem reserva para ' . number_format($liquidez, 1) . ' mês(es) de gastos',
                'acao' => 'Ideal ter pelo menos 3 meses de reserva',
                'icone' => 'ph-coins'
            ];
        }

        return $alertas;
    }

    // Gera recomendações financeiras personalizadas
    public function getRecomendacoesFinanceiras($idUsuario, $mes = null, $ano = null): array
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $recomendacoes = [];

        // Dados para análise
        $receitas = $this->getTotalReceitasRecebidas($idUsuario, $mes, $ano);
        $despesas = $this->somarDespesasPagas($idUsuario, $mes, $ano);
        $taxaPoupanca = $this->getTaxaPoupanca($idUsuario, $mes, $ano);
        $receitasMesAnterior = $this->getReceitasMesAnterior($idUsuario, $mes, $ano);
        $despesasMesAnterior = $this->getDespesasPagasMesAnterior($idUsuario, $mes, $ano);

        // 1. Recomendações baseadas em tendências
        if ($receitas > 0 && $receitasMesAnterior > 0) {
            $crescimentoReceitas = (($receitas - $receitasMesAnterior) / $receitasMesAnterior) * 100;

            if ($crescimentoReceitas >= 20) {
                $recomendacoes[] = [
                    'tipo' => 'sucesso',
                    'titulo' => 'Excelente Crescimento de Renda!',
                    'mensagem' => 'Suas receitas cresceram ' . number_format($crescimentoReceitas, 1) . '% em relação ao mês anterior',
                    'acao' => 'Aproveite para aumentar a poupança ou investimentos',
                    'icone' => 'ph-trend-up'
                ];
            }
        }

        if ($despesas > 0 && $despesasMesAnterior > 0) {
            $crescimentoDespesas = (($despesas - $despesasMesAnterior) / $despesasMesAnterior) * 100;

            if ($crescimentoDespesas >= 20) {
                $recomendacoes[] = [
                    'tipo' => 'atencao',
                    'titulo' => 'Aumento Significativo de Gastos',
                    'mensagem' => 'Suas despesas aumentaram ' . number_format($crescimentoDespesas, 1) . '% em relação ao mês anterior',
                    'acao' => 'Revise seus gastos para identificar possíveis economias',
                    'icone' => 'ph-trend-down'
                ];
            }
        }

        // 2. Recomendações de Poupança
        if ($taxaPoupanca >= 20) {
            $recomendacoes[] = [
                'tipo' => 'sucesso',
                'titulo' => 'Poupança Exemplar!',
                'mensagem' => 'Você está poupando ' . number_format($taxaPoupanca, 1) . '% da sua renda',
                'acao' => 'Considere investir parte do dinheiro para fazer render mais',
                'icone' => 'ph-chart-line'
            ];
        } elseif ($taxaPoupanca >= 10) {
            $recomendacoes[] = [
                'tipo' => 'info',
                'titulo' => 'Boa Poupança',
                'mensagem' => 'Você está poupando ' . number_format($taxaPoupanca, 1) . '% da sua renda',
                'acao' => 'Tente aumentar gradualmente para 15-20%',
                'icone' => 'ph-target'
            ];
        }

        // 3. Recomendações de Categorias
        $gastosCategorias = $this->listarGastosPorCategoria($idUsuario, $mes, $ano);
        if (!empty($gastosCategorias)) {
            $categoriaMaiorGasto = $gastosCategorias[0];
            $totalGastos = array_sum(array_column($gastosCategorias, 'total'));
            $percentualMaiorCategoria = ($categoriaMaiorGasto['total'] / $totalGastos) * 100;

            if ($percentualMaiorCategoria >= 40) {
                $recomendacoes[] = [
                    'tipo' => 'atencao',
                    'titulo' => 'Concentração de Gastos',
                    'mensagem' => $categoriaMaiorGasto['nome_categoria'] . ' representa ' . number_format($percentualMaiorCategoria, 1) . '% dos seus gastos',
                    'acao' => 'Diversifique seus gastos para melhor controle financeiro',
                    'icone' => 'ph-chart-pie'
                ];
            }
        }

        // 4. Recomendações de Cartões
        $cartoesComLimites = $this->getCartoesComLimites($idUsuario);
        $cartoesComBaixaUtilizacao = array_filter($cartoesComLimites, function ($cartao) {
            return $cartao['utilizacao_percentual'] < 30;
        });

        if (count($cartoesComBaixaUtilizacao) > 2) {
            $recomendacoes[] = [
                'tipo' => 'info',
                'titulo' => 'Otimização de Cartões',
                'mensagem' => 'Você tem ' . count($cartoesComBaixaUtilizacao) . ' cartões com baixa utilização',
                'acao' => 'Considere cancelar cartões não utilizados para simplificar o controle',
                'icone' => 'ph-credit-card'
            ];
        }

        return $recomendacoes;
    }

    // Calcula score financeiro do usuário (0-100)
    public function getScoreFinanceiro($idUsuario, $mes = null, $ano = null): array
    {
        $mes = $mes ?? date('n');
        $ano = $ano ?? date('Y');

        $score = 0;
        $fatores = [];

        // Taxa de poupança (0-25 pontos)
        $taxaPoupanca = $this->getTaxaPoupanca($idUsuario, $mes, $ano);
        if ($taxaPoupanca >= 20) {
            $pontosPoupanca = 25;
        } elseif ($taxaPoupanca >= 15) {
            $pontosPoupanca = 20;
        } elseif ($taxaPoupanca >= 10) {
            $pontosPoupanca = 15;
        } elseif ($taxaPoupanca >= 5) {
            $pontosPoupanca = 10;
        } elseif ($taxaPoupanca >= 0) {
            $pontosPoupanca = 5;
        } else {
            $pontosPoupanca = 0;
        }
        $score += $pontosPoupanca;
        $fatores[] = ['nome' => 'Taxa de Poupança', 'pontos' => $pontosPoupanca, 'max' => 25];

        // Endividamento (0-25 pontos)
        $endividamento = $this->getTaxaEndividamento($idUsuario, $mes, $ano);
        if ($endividamento <= 20) {
            $pontosEndividamento = 25;
        } elseif ($endividamento <= 30) {
            $pontosEndividamento = 20;
        } elseif ($endividamento <= 40) {
            $pontosEndividamento = 15;
        } elseif ($endividamento <= 50) {
            $pontosEndividamento = 10;
        } else {
            $pontosEndividamento = 0;
        }
        $score += $pontosEndividamento;
        $fatores[] = ['nome' => 'Controle de Endividamento', 'pontos' => $pontosEndividamento, 'max' => 25];

        // Liquidez (0-25 pontos)
        $liquidez = $this->getLiquidez($idUsuario, $mes, $ano);
        if ($liquidez >= 6) {
            $pontosLiquidez = 25;
        } elseif ($liquidez >= 3) {
            $pontosLiquidez = 20;
        } elseif ($liquidez >= 2) {
            $pontosLiquidez = 15;
        } elseif ($liquidez >= 1) {
            $pontosLiquidez = 10;
        } else {
            $pontosLiquidez = 0;
        }
        $score += $pontosLiquidez;
        $fatores[] = ['nome' => 'Reserva de Emergência', 'pontos' => $pontosLiquidez, 'max' => 25];

        // Eficiência de pagamentos (0-25 pontos)
        $eficiencia = $this->getEficienciaPagamentos($idUsuario, $mes, $ano);
        if ($eficiencia >= 90) {
            $pontosEficiencia = 25;
        } elseif ($eficiencia >= 80) {
            $pontosEficiencia = 20;
        } elseif ($eficiencia >= 70) {
            $pontosEficiencia = 15;
        } elseif ($eficiencia >= 60) {
            $pontosEficiencia = 10;
        } else {
            $pontosEficiencia = 0;
        }
        $score += $pontosEficiencia;
        $fatores[] = ['nome' => 'Controle de Pagamentos', 'pontos' => $pontosEficiencia, 'max' => 25];

        // Classificação do score
        if ($score >= 90) {
            $classificacao = 'Excelente';
            $cor = 'verde';
        } elseif ($score >= 75) {
            $classificacao = 'Muito Bom';
            $cor = 'azul';
        } elseif ($score >= 60) {
            $classificacao = 'Bom';
            $cor = 'amarelo';
        } elseif ($score >= 40) {
            $classificacao = 'Regular';
            $cor = 'laranja';
        } else {
            $classificacao = 'Precisa Melhorar';
            $cor = 'vermelho';
        }

        return [
            'score' => $score,
            'classificacao' => $classificacao,
            'cor' => $cor,
            'fatores' => $fatores
        ];
    }
}
