<?php
require_once BASE_PATH . '/app/config/db_config.php';

class MenuModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDatabase();
    }

    public function somarDespesasCartao($idUsuario): float
    {
        $sql = "SELECT SUM(valor) as total 
        FROM faturas 
        WHERE id_usuario = ? 
        AND MONTH(data_vencimento) = MONTH(CURRENT_DATE())
        AND YEAR(data_vencimento) = YEAR(CURRENT_DATE())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (float) ($result['total'] ?? 0);
    }

    public function somarDespesas($idUsuario): float
    {
        $sql = "SELECT SUM(valor) as total 
        FROM despesas 
        WHERE id_usuario = ? 
        AND MONTH(data_vencimento) = MONTH(CURRENT_DATE())
        AND YEAR(data_vencimento) = YEAR(CURRENT_DATE())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (float) ($result['total'] ?? 0);
    }

    public function somarDespesasPendentes($idUsuario): float
    {
        $sql = "SELECT SUM(valor) as total 
        FROM despesas 
        WHERE id_usuario = ? 
        AND status = 'pendente'
        AND MONTH(data_vencimento) = MONTH(CURRENT_DATE())
        AND YEAR(data_vencimento) = YEAR(CURRENT_DATE())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return (float) ($result['total'] ?? 0);
    }

    public function somarDespesasPagas($idUsuario): float
    {
        $sql = "SELECT SUM(valor_pago) as total 
        FROM despesas 
        WHERE id_usuario = ? 
        AND status = 'pago'
        AND MONTH(data_pagamento) = MONTH(CURRENT_DATE())
        AND YEAR(data_pagamento) = YEAR(CURRENT_DATE())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
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


    public function listarGastosPorCartao($idUsuario): array
    {
        $sql = "SELECT c.nome_cartao, SUM(f.valor) as total 
        FROM faturas f
        INNER JOIN cartoes c ON f.id_cartao = c.id_cartao
        WHERE f.id_usuario = ?
        AND MONTH(f.data_vencimento) = MONTH(CURRENT_DATE())
        AND YEAR(f.data_vencimento) = YEAR(CURRENT_DATE())
        GROUP BY c.nome_cartao
        ORDER BY total DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
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

    public function listarGastosPorCategoria($idUsuario): array
    {
        $sql = "SELECT 
                d.id_categoria,
                c.nome_categoria,
                SUM(d.valor) AS total
            FROM despesas d
            INNER JOIN categorias c ON d.id_categoria = c.id_categoria
            WHERE d.id_usuario = ?
              AND MONTH(d.data_vencimento) = MONTH(CURRENT_DATE())
              AND YEAR(d.data_vencimento) = YEAR(CURRENT_DATE())
            GROUP BY d.id_categoria, c.nome_categoria
            ORDER BY total DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
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


    public function buscarDespesasPorSubcategoria($idUsuario, $idCategoria): array
    {
        $sql = "SELECT s.nome_subcategoria, SUM(d.valor) as total 
        FROM despesas d
        INNER JOIN subcategorias s ON d.id_subcategoria = s.id_subcategoria
        WHERE d.id_usuario = ?
        AND d.id_categoria = ?
        AND MONTH(data_vencimento) = MONTH(CURRENT_DATE())
        AND YEAR(data_vencimento) = YEAR(CURRENT_DATE())
        GROUP BY s.nome_subcategoria
        ORDER BY total DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $idUsuario, $idCategoria);
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

    public function getTotalReceitasMes(int $idUsuario): float
    {
        $sql = "
        SELECT COALESCE(SUM(valor),0) AS total
        FROM receitas
        WHERE id_usuario = ?
          AND MONTH(data_vencimento) = MONTH(CURRENT_DATE())
          AND YEAR(data_vencimento)  = YEAR(CURRENT_DATE())
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (float) $row['total'];
    }

    public function listarReceitasPorCategoria($idUsuario): array
    {
        $sql = "SELECT c.nome_categoria, SUM(r.valor) as total 
        FROM receitas r
        INNER JOIN categorias c ON r.id_categoria = c.id_categoria
        WHERE r.id_usuario = ?
        AND MONTH(data_vencimento) = MONTH(CURRENT_DATE())
        AND YEAR(data_vencimento) = YEAR(CURRENT_DATE())
        GROUP BY c.nome_categoria
        ORDER BY total DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
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
    public function getTotalReceitasRecebidas(int $idUsuario): float
    {
        $sql = "
        SELECT COALESCE(SUM(valor_recebido),0) AS total
        FROM receitas 
        WHERE id_usuario = ? 
            AND status = 'recebido'
            AND MONTH(data_vencimento) = MONTH(CURRENT_DATE())
            AND YEAR(data_vencimento)  = YEAR(CURRENT_DATE())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        return (float) $stmt->get_result()->fetch_assoc()['total'];
    }

    // Total de receitas pendentes (previstas)
    public function getTotalReceitasPendentes(int $idUsuario): float
    {
        $sql = "
        SELECT COALESCE(SUM(valor),0) AS total
        FROM receitas 
        WHERE id_usuario = ? 
            AND status = 'previsto'
            AND MONTH(data_vencimento) = MONTH(CURRENT_DATE())
            AND YEAR(data_vencimento)  = YEAR(CURRENT_DATE())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
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
}
