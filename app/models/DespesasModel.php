<?php
require_once BASE_PATH . '/app/config/db_config.php';

class DespesasModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDatabase();
    }
    public function buscarTodos($idUsuario): array
    {
        $idUsuario = (int)$idUsuario;

        $sql = "
        SELECT 
            d.id_despesa,
            d.grupo_despesa,
            d.id_usuario,
            d.id_categoria,
            d.id_subcategoria,
            d.id_forma_transacao,
            d.id_conta,
            d.id_cartao,
            d.descricao,
            d.valor,
            d.juros,
            d.desconto,
            d.valor_pago,
            d.data_vencimento,
            d.data_pagamento,
            d.status,
            d.recorrente,
            d.parcelado,
            d.numero_parcelas,
            d.total_parcelas,
            d.ultima_parcela,
            d.anexo,
            d.observacoes,
            d.criado_em,
            d.atualizado_em,
            c.nome_categoria,
            s.nome_subcategoria,
            f.nome AS nome_forma,
            cb.nome_conta,
            cbs.saldo_atual,
            ct.nome_cartao
        FROM despesas d
        LEFT JOIN categorias c ON d.id_categoria = c.id_categoria
        LEFT JOIN subcategorias s ON d.id_subcategoria = s.id_subcategoria
        LEFT JOIN formas_transacao f ON d.id_forma_transacao = f.id_forma_transacao
        LEFT JOIN contas_bancarias cb ON d.id_conta = cb.id_conta
        LEFT JOIN contas_bancarias cbs ON cbs.id_conta = d.id_conta
        LEFT JOIN cartoes ct ON d.id_cartao = ct.id_cartao
        WHERE d.id_usuario = $idUsuario
        ORDER BY d.data_vencimento ASC
    ";

        $result = $this->conn->query($sql);

        $despesas = [];
        while ($row = $result->fetch_assoc()) {
            $despesas[] = $row;
        }

        return $despesas;
    }
    public function buscarCategorias($idUsuario): array
    {
        $idUsuario = (int)$idUsuario;

        $sql = "SELECT id_categoria, nome_categoria FROM categorias 
        WHERE tipo = 'despesa'
        AND ativa = 1 
        AND (id_usuario = $idUsuario or categoria_padrao = 1)
        ORDER BY nome_categoria ASC";


        $result = $this->conn->query($sql);

        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }

        return $categorias;
    }
    public function buscarSubcategorias($idUsuario): array
    {
        $idUsuario = (int)$idUsuario;

        $sql = "SELECT id_subcategoria, nome_subcategoria, id_categoria
        FROM subcategorias 
        WHERE ativa = 1 
        AND (id_usuario = $idUsuario or subcategoria_padrao = 1)
        ORDER BY nome_subcategoria ASC";

        $result = $this->conn->query($sql);

        $subcategorias = [];
        while ($row = $result->fetch_assoc()) {
            $subcategorias[] = $row;
        }

        return $subcategorias;
    }
    public function buscarFormasTransacao($idUsuario): array
    {
        $idUsuario = (int)$idUsuario;

        $sql = "SELECT id_forma_transacao, nome, uso
FROM formas_transacao 
WHERE uso IN ('pagamento', 'ambos')
AND ativa = 1
AND id_forma_transacao != 2
AND (id_usuario = $idUsuario OR padrao = 1)
ORDER BY nome ASC";


        $result = $this->conn->query($sql);

        $formas = [];
        while ($row = $result->fetch_assoc()) {
            $formas[] = $row;
        }

        return $formas;
    }
    public function buscarContas($idUsuario): array
    {
        $sql = "SELECT id_conta, nome_conta FROM contas_bancarias 
            WHERE id_usuario = $idUsuario AND ativa = 1";

        $result = $this->conn->query($sql);
        $dados = [];
        while ($row = $result->fetch_assoc()) {
            $dados[] = $row;
        }
        return $dados;
    }
    public function buscarCartoes($idUsuario): array
    {
        $sql = "SELECT id_cartao, nome_cartao, id_conta, vencimento_fatura, dia_fechamento FROM cartoes 
        WHERE id_usuario = $idUsuario";


        $result = $this->conn->query($sql);
        $dados = [];
        while ($row = $result->fetch_assoc()) {
            $dados[] = $row;
        }
        return $dados;
    }
    public function listarPorCartao($idUsuario, $idCartao, $mes, $ano)
    {
        $sql = "SELECT d.*, c.nome_categoria, s.nome_subcategoria
            FROM despesas d
            LEFT JOIN categorias c ON d.id_categoria = c.id_categoria
            LEFT JOIN subcategorias s ON d.id_subcategoria = s.id_subcategoria
            WHERE d.id_usuario = ? AND d.id_cartao = ?
            AND MONTH(d.data_vencimento) = ? AND YEAR(d.data_vencimento) = ?
            ORDER BY d.data_vencimento DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $idUsuario, $idCartao, $mes, $ano);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getInfoCartao($idUsuario, $idCartao)
    {
        $sql = "SELECT * FROM cartoes WHERE id_usuario = ? AND id_cartao = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $idUsuario, $idCartao);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function getValorFaturaAberta($idCartao, $idUsuario)
    {
        $sql = "SELECT SUM(valor) as total FROM despesas 
            WHERE id_cartao = ? AND id_usuario = ? AND status = 'pendente'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $idCartao, $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row ? $row['total'] : 0;
    }
    public function getValorFaturaMensal($idCartao, $idUsuario, $mes, $ano)
    {
        $sql = "SELECT SUM(valor) as total FROM despesas 
            WHERE id_cartao = ? AND id_usuario = ? 
            AND status = 'pendente'
            AND MONTH(data_vencimento) = ? AND YEAR(data_vencimento) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $idCartao, $idUsuario, $mes, $ano);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row['total'] : 0;
    }
    public function quitarFatura($idCartao, $idConta, $valor, $idUsuario, $mes, $ano)
    {
        $this->conn->begin_transaction();

        try {
            // Pagar só as despesas da fatura do mês selecionado
            $sql = "UPDATE despesas 
                SET status = 'pago', data_pagamento = NOW(), valor_pago = valor 
                WHERE id_cartao = ? AND id_usuario = ? AND status = 'pendente'
                AND MONTH(data_vencimento) = ? AND YEAR(data_vencimento) = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iiii", $idCartao, $idUsuario, $mes, $ano);
            $stmt->execute();

            // Atualizar saldo da conta
            $sqlConta = "UPDATE contas_bancarias SET saldo_atual = saldo_atual - ? 
                     WHERE id_conta = ? AND id_usuario = ?";
            $stmtConta = $this->conn->prepare($sqlConta);
            $stmtConta->bind_param("dii", $valor, $idConta, $idUsuario);
            $stmtConta->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    public function atualizarDespesaCartao($dados, $modo): bool
    {
        $idUsuario = (int) $dados['id_usuario'];
        $idDespesa = (int) $dados['id_despesa'];

        $valor = (float) $dados['valor'];
        $juros = (float) ($dados['juros'] ?? 0.00);
        $desconto = (float) ($dados['desconto'] ?? 0.00);
        $valorPago = ($dados['status'] === 'pago') ? ($valor + $juros - $desconto) : 0.00;

        $dataPagamento = ($dados['status'] === 'pago' && !empty($dados['data_pagamento']))
            ? $dados['data_pagamento']
            : null;

        $status = $this->conn->real_escape_string($dados['status']);
        $descricao = $this->conn->real_escape_string($dados['descricao']);
        $obs = $this->conn->real_escape_string($dados['observacoes']);

        $categoria = (int) $dados['categoria'];
        $subcategoria = (int) $dados['subcategoria'];
        $idConta = (int) $dados['id_conta'];
        $idCartao = (int) $dados['id_cartao'];
        $forma = isset($dados['forma']) ? (int) $dados['forma'] : 0;
        $dataVencimento = $this->conn->real_escape_string($dados['data_vencimento']);

        $parcelado = isset($dados['parcelado']) ? (int) $dados['parcelado'] : 0;
        $numeroParcelas = (int) ($dados['numero_parcelas'] ?? 1);
        $totalParcelas = (int) ($dados['total_parcelas'] ?? 1);

        // Buscar grupo
        $stmtGrupo = $this->conn->prepare("SELECT grupo_despesa FROM despesas WHERE id_despesa = ? AND id_usuario = ?");
        $stmtGrupo->bind_param("ii", $idDespesa, $idUsuario);
        $stmtGrupo->execute();
        $res = $stmtGrupo->get_result()->fetch_assoc();
        $grupo = $res['grupo_despesa'] ?? null;

        $filtro = "id_despesa = $idDespesa";
        if (in_array($modo, ['futuras', 'todas']) && $grupo) {
            $filtro = "grupo_despesa = '$grupo'";
            if ($modo === 'futuras') {
                $filtro .= " AND numero_parcelas >= $numeroParcelas";
            }
        }

        $sql = "
    UPDATE despesas SET
        id_categoria = $categoria,
        id_subcategoria = $subcategoria,
        descricao = '$descricao',
        valor = $valor,
        juros = $juros,
        desconto = $desconto,
        valor_pago = $valorPago,
        id_conta = $idConta,
        id_cartao = $idCartao,
        id_forma_transacao = $forma,
        data_vencimento = '$dataVencimento',
        data_pagamento = " . ($dataPagamento ? "'$dataPagamento'" : "NULL") . ",
        status = '$status',
        parcelado = $parcelado,
        numero_parcelas = $numeroParcelas,
        total_parcelas = $totalParcelas,
        observacoes = '$obs',
        atualizado_em = NOW()
    WHERE id_usuario = $idUsuario AND $filtro
    ";

        return $this->conn->query($sql);
    }
    public function salvarDespesa(array $dados): bool
    {
        // normaliza dados básicos
        $descricao = ucwords(strtolower(trim($dados['descricao'])));
        $idUsuario = (int)$dados['id_usuario'];
        $valor     = (float)$dados['valor'];

        // calcula valor_pago, juros e desconto
        $valorPago = isset($dados['valor_pago']) ? (float)$dados['valor_pago'] : 0.00;
        $juros     = 0.00;
        $desconto  = 0.00;
        if ($valorPago < $valor) {
            $desconto = $valor - $valorPago;
        } elseif ($valorPago > $valor) {
            $juros = $valorPago - $valor;
        }

        // status e data de pagamento
        $status = $dados['status'];
        if ($status !== 'pago') {
            $valorPago = 0.00;
            $juros = 0.00;
            $desconto = 0.00;
        }
        $dataPagamento = ($status === 'pago' && !empty($dados['data_pagamento']))
            ? $dados['data_pagamento']
            : null;

        // flags
        $recorrente = (isset($dados['recorrente']) && $dados['recorrente'] === '1') ? 1 : 0;
        $parcelado  = (isset($dados['parcelado'])  && $dados['parcelado']  === '1') ? 1 : 0;

        // conta, cartão e forma
        $idConta          = (isset($dados['id_conta']) && is_numeric($dados['id_conta']))
            ? (int)$dados['id_conta']
            : null;
        $idCartao         = !empty($dados['id_cartao']) ? (int)$dados['id_cartao'] : null;
        $idFormaTransacao = $idCartao ? 2 : (int)$dados['forma'];

        // parcelas e grupo
        $totalParcelas  = ((int)($dados['total_parcelas'] ?? 1) > 0)
            ? (int)$dados['total_parcelas']
            : 1;
        $numeroParcelas = $parcelado
            ? ((int)($dados['numero_parcelas'] ?? 1) > 0
                ? (int)$dados['numero_parcelas']
                : 1)
            : 1;

        if ($parcelado && $totalParcelas > 1) {
            $grupoDespesa = uniqid('grp_');
        } elseif ($recorrente && !$parcelado) {
            // gera um grupo também para recorrentes
            $grupoDespesa = uniqid('rec_');
        } else {
            $grupoDespesa = null;
        }


        // prepara o INSERT
        $sql = "
        INSERT INTO despesas (
            id_usuario,
            id_categoria,
            id_subcategoria,
            descricao,
            valor,
            valor_pago,
            juros,
            desconto,
            id_conta,
            id_cartao,
            id_forma_transacao,
            data_vencimento,
            data_pagamento,
            status,
            recorrente,
            parcelado,
            numero_parcelas,
            total_parcelas,
            ultima_parcela,
            observacoes,
            grupo_despesa,
            criado_em,
            atualizado_em
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
        )
    ";
        $stmt = $this->conn->prepare($sql);

        // base de data
        $baseDate = new \DateTime($dados['data_vencimento']);
        $dv       = $baseDate->format('Y-m-d');

        //
        // 1) INSERE O LANÇAMENTO PRINCIPAL
        //
        // se for parcelado com múltiplas parcelas, a primeira NÃO é a última
        $ultimaParcela = ($parcelado && $totalParcelas > 1 && $numeroParcelas < $totalParcelas) ? 0 : 1;

        $stmt->bind_param(
            "iiisddddiiisssiiiisss",
            $idUsuario,
            $dados['categoria'],
            $dados['subcategoria'],
            $descricao,
            $valor,
            $valorPago,
            $juros,
            $desconto,
            $idConta,
            $idCartao,
            $idFormaTransacao,
            $dv,
            $dataPagamento,
            $status,
            $recorrente,
            $parcelado,
            $numeroParcelas,
            $totalParcelas,
            $ultimaParcela,
            $dados['observacoes'],
            $grupoDespesa
        );
        $stmt->execute();

        // 1.1) Se já foi pago, debita da conta selecionada
        if ($status === 'pago' && $idConta) {
            // valorPago já está calculado acima
            $this->conn->query("
        UPDATE contas_bancarias
        SET saldo_atual = saldo_atual - {$valorPago}
        WHERE id_conta = {$idConta} AND id_usuario = {$idUsuario}
    ");
        }


        //
        // 2) GERA AS PARCELAS (se for parcelado)
        if ($parcelado && $totalParcelas > 1) {
            // a partir da próxima parcela
            for ($i = $numeroParcelas + 1; $i <= $totalParcelas; $i++) {
                // calcula o deslocamento em meses
                $diff = $i - $numeroParcelas;
                $next = (clone $baseDate)->modify("+{$diff} month");
                $venc = $next->format('Y-m-d');

                $vpRec   = 0.00;
                $juRec   = 0.00;
                $deRec   = 0.00;
                $dpRec   = null;
                $stRec   = 'pendente';
                $recRec  = 0;
                $parcRec = 1;
                $numRec  = $i;
                $totRec  = $totalParcelas;
                $ultRec  = ($i === $totalParcelas) ? 1 : 0;
                $obsRec  = $dados['observacoes'];
                $grpRec  = $grupoDespesa;

                $stmt->bind_param(
                    "iiisddddiiisssiiiisss",
                    $idUsuario,
                    $dados['categoria'],
                    $dados['subcategoria'],
                    $descricao,
                    $valor,
                    $vpRec,
                    $juRec,
                    $deRec,
                    $idConta,
                    $idCartao,
                    $idFormaTransacao,
                    $venc,
                    $dpRec,
                    $stRec,
                    $recRec,
                    $parcRec,
                    $numRec,
                    $totRec,
                    $ultRec,
                    $obsRec,
                    $grpRec
                );
                $stmt->execute();
            }
        }


        //
        // 3) GERA AS RECORRÊNCIAS (se for recorrente e NÃO parcelado)
        //
        if ($recorrente && !$parcelado) {
            for ($i = 1; $i < 12; $i++) {
                $next   = (clone $baseDate)->modify("+{$i} month");
                $venc   = $next->format('Y-m-d');
                $vpRec  = 0.00;
                $juRec  = 0.00;
                $deRec  = 0.00;
                $dpRec  = null;
                $stRec  = 'pendente';
                $recRec = 1;
                $parcR  = 0;
                $numR   = 1;
                $totR   = 1;
                $ultR   = 1;
                $obsR   = $dados['observacoes'];
                $grpR   = $grupoDespesa;

                $stmt->bind_param(
                    "iiisddddiiisssiiiisss",
                    $idUsuario,
                    $dados['categoria'],
                    $dados['subcategoria'],
                    $descricao,
                    $valor,
                    $vpRec,
                    $juRec,
                    $deRec,
                    $idConta,
                    $idCartao,
                    $idFormaTransacao,
                    $venc,
                    $dpRec,
                    $stRec,
                    $recRec,
                    $parcR,
                    $numR,
                    $totR,
                    $ultR,
                    $obsR,
                    $grpR
                );
                $stmt->execute();
            }
        }


        return true;
    }

    public function atualizarDespesa(array $dados): bool
    {
        // 1) Normaliza campos básicos
        $idUsuario      = (int)   $dados['id_usuario'];
        $idDespesa      = (int)   $dados['id_despesa'];
        $valor          = (float) $dados['valor'];

        // 2) Valor pago só se veio no post; senão zero
        $valorPago      = isset($dados['valor_pago'])
            ? (float)$dados['valor_pago']
            : 0.00;

        // 3) Status e data de pagamento
        $status         = in_array($dados['status'], ['pendente', 'pago', 'atrasado'])
            ? $dados['status']
            : 'pendente';
        $dataPagamento  = ($status === 'pago' && !empty($dados['data_pagamento']))
            ? $dados['data_pagamento']
            : null;
        if ($status !== 'pago') {
            // se não for pago, zera valor pago
            $valorPago     = 0.00;
            $dataPagamento = null;
        }

        // 4) Recalcula juros e desconto
        $juros = 0.00;
        $desconto = 0.00;
        if ($status === 'pago') {
            if ($valorPago < $valor) {
                $desconto = $valor - $valorPago;
            } elseif ($valorPago > $valor) {
                $juros = $valorPago - $valor;
            }
        }

        // 5) Outras flags e campos
        $recorrente = (isset($dados['recorrente']) && $dados['recorrente'] === '1') ? 1 : 0;
        $parcelado = isset($dados['parcelado']) && $dados['parcelado'] === '1' ? 1 : 0;
        $numeroParcelas = (int) ($dados['numero_parcelas'] ?? 1);
        $totalParcelas  = (int) ($dados['total_parcelas']  ?? 1);
        $modoEdicao     = $dados['modo_edicao']       ?? 'somente';

        $categoria      = (int)   $dados['categoria'];
        $subcategoria   = (int)   $dados['subcategoria'];
        $idConta        = (int)   $dados['id_conta'];
        $idCartao       = !empty($dados['id_cartao'])
            ? (int)$dados['id_cartao']
            : null;
        $forma          = (int)   $dados['forma'];
        $dataVencimento = $this->conn->real_escape_string($dados['data_vencimento']);
        $descricao      = $this->conn->real_escape_string($dados['descricao']);
        $obs            = $this->conn->real_escape_string($dados['observacoes']);

        // 6) Carrega o estado anterior para ajustar saldo
        $stmtOld = $this->conn->prepare("
        SELECT status, valor_pago, id_conta
        FROM despesas
        WHERE id_despesa = ? AND id_usuario = ?
    ");
        $stmtOld->bind_param("ii", $idDespesa, $idUsuario);
        $stmtOld->execute();
        $original = $stmtOld->get_result()->fetch_assoc();

        // 7) Se passou de pago → não pago, devolve o valor antigo
        if (
            $original['status'] === 'pago'
            && $status !== 'pago'
            && !empty($original['id_conta'])
        ) {
            $this->conn->query("
            UPDATE contas_bancarias
            SET saldo_atual = saldo_atual + {$original['valor_pago']}
            WHERE id_conta = {$original['id_conta']}
        ");
        }

        // 8) Se edição apenas desta parcela
        if ($modoEdicao === 'somente') {
            // pega ultima_parcela
            $ultimaParcela = 0;
            $stmtUpa = $this->conn->prepare("
            SELECT ultima_parcela
            FROM despesas
            WHERE id_despesa = ? AND id_usuario = ?
        ");
            $stmtUpa->bind_param("ii", $idDespesa, $idUsuario);
            $stmtUpa->execute();
            if ($row = $stmtUpa->get_result()->fetch_assoc()) {
                $ultimaParcela = (int)$row['ultima_parcela'];
            }

            // atualiza só esta
            $sql = "
            UPDATE despesas SET
                id_categoria        = ?,
                id_subcategoria     = ?,
                descricao           = ?,
                valor               = ?,
                valor_pago          = ?,
                juros               = ?,
                desconto            = ?,
                id_conta            = ?,
                id_cartao           = ?,
                id_forma_transacao  = ?,
                data_vencimento     = ?,
                data_pagamento      = ?,
                status              = ?,
                recorrente          = ?,
                parcelado           = ?,
                numero_parcelas     = ?,
                total_parcelas      = ?,
                ultima_parcela      = ?,
                observacoes         = ?,
                atualizado_em       = NOW()
            WHERE id_despesa = ? AND id_usuario = ?
        ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "iisddddiiisssiiiiisii",
                $categoria,
                $subcategoria,
                $descricao,
                $valor,
                $valorPago,
                $juros,
                $desconto,
                $idConta,
                $idCartao,
                $forma,
                $dataVencimento,
                $dataPagamento,
                $status,
                $recorrente,
                $parcelado,
                $numeroParcelas,
                $totalParcelas,
                $ultimaParcela,
                $obs,
                $idDespesa,
                $idUsuario
            );
            $ok = $stmt->execute();

            if ($ok) {
                // 9a) se passou de pendente→pago, debita totalmente
                if ($original['status'] !== 'pago' && $status === 'pago') {
                    $this->conn->query("
                    UPDATE contas_bancarias
                    SET saldo_atual = saldo_atual - {$valorPago}
                    WHERE id_conta = {$idConta} AND id_usuario = {$idUsuario}
                ");
                }
                // 9b) se já estava pago e mudou o valor, ajusta diferença
                elseif (
                    $original['status'] === 'pago'
                    && $status === 'pago'
                    && $valorPago !== (float)$original['valor_pago']
                ) {
                    $delta = $valorPago - (float)$original['valor_pago'];
                    $this->conn->query("
                    UPDATE contas_bancarias
                    SET saldo_atual = saldo_atual - {$delta}
                    WHERE id_conta = {$idConta} AND id_usuario = {$idUsuario}
                ");
                }
            }
            return $ok;
        }

        // 10) Atualização em lote (futuras/todas)
        $stmtG = $this->conn->prepare("
        SELECT grupo_despesa
        FROM despesas
        WHERE id_despesa = ? AND id_usuario = ?
    ");
        $stmtG->bind_param("ii", $idDespesa, $idUsuario);
        $stmtG->execute();
        $grupo = $stmtG->get_result()->fetch_assoc()['grupo_despesa'] ?? null;
        if (!$grupo) {
            return false;
        }

        $filtro = $modoEdicao === 'futuras'
            ? "AND numero_parcelas >= {$numeroParcelas}"
            : "";

        $sqlBatch = "
        UPDATE despesas SET
            id_categoria       = {$categoria},
            id_subcategoria    = {$subcategoria},
            descricao          = '{$descricao}',
            valor              = {$valor},
            valor_pago         = {$valorPago},
            juros              = {$juros},
            desconto           = {$desconto},
            id_conta           = {$idConta},
            id_cartao          = " . ($idCartao ?? 'NULL') . ",
            id_forma_transacao = {$forma},
            status             = '{$status}',
            recorrente         = {$recorrente},
            parcelado          = {$parcelado},
            total_parcelas     = {$totalParcelas},
            observacoes        = '{$obs}',
            atualizado_em      = NOW()
        WHERE id_usuario = {$idUsuario}
          AND grupo_despesa = '{$grupo}'
          {$filtro}
    ";
        return $this->conn->query($sqlBatch);
    }

    public function confirmarPagamento(int $idDespesa, int $idUsuario, string $dataPagamento, int $idConta, float $valor): bool
    {
        // 1) Busca a despesa garantindo o usuário dono dela
        $sql = "SELECT valor, status, id_conta, id_forma_transacao 
            FROM despesas 
            WHERE id_despesa = ? AND id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $idDespesa, $idUsuario);
        $stmt->execute();
        $despesa = $stmt->get_result()->fetch_assoc();
        if (!$despesa || $despesa['status'] === 'pago') {
            return false;
        }

        // 2) Impede cartão
        $stmtF = $this->conn->prepare("SELECT nome FROM formas_transacao WHERE id_forma_transacao = ?");
        $stmtF->bind_param("i", $despesa['id_forma_transacao']);
        $stmtF->execute();
        $nomeForma = $stmtF->get_result()->fetch_assoc()['nome'] ?? '';
        if (strtolower($nomeForma) === 'cartão de crédito') {
            return false;
        }

        // 3) Busca saldo da conta do usuário
        $stmtC = $this->conn->prepare(
            "SELECT saldo_atual FROM contas_bancarias 
         WHERE id_conta = ? AND id_usuario = ?"
        );
        $stmtC->bind_param("ii", $idConta, $idUsuario);
        $stmtC->execute();
        $rowC = $stmtC->get_result()->fetch_assoc();
        $saldoAtual = (float)($rowC['saldo_atual'] ?? 0);

        // 4) calcula juros/desconto
        $orig = (float)$despesa['valor'];
        $juros = $desconto = 0.00;
        if ($valor < $orig) {
            $desconto = $orig - $valor;
        } elseif ($valor > $orig) {
            $juros = $valor - $orig;
        }

        // 5) Atualiza a despesa
        $upd = $this->conn->prepare("
        UPDATE despesas
        SET status = 'pago',
            data_pagamento = ?,
            valor_pago     = ?,
            id_conta       = ?,
            desconto       = ?,
            juros          = ?
        WHERE id_despesa = ? AND id_usuario = ?
    ");
        $upd->bind_param(
            "sddddii",
            $dataPagamento,
            $valor,
            $idConta,
            $desconto,
            $juros,
            $idDespesa,
            $idUsuario
        );
        if (!$upd->execute()) {
            return false;
        }

        // 6) Debita saldo na conta do usuário
        $this->conn->query("
        UPDATE contas_bancarias
        SET saldo_atual = saldo_atual - {$valor}
        WHERE id_conta = {$idConta} AND id_usuario = {$idUsuario}
    ");

        return true;
    }

    public function excluirDespesa(int $id, int $idUsuario, string $escopo = 'somente'): bool
    {
        // Buscar dados da despesa antes de excluir
        $sql = "SELECT valor_pago, id_conta, status, grupo_despesa, numero_parcelas FROM despesas WHERE id_despesa = ? AND id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $despesa = $result->fetch_assoc();

        if (!$despesa) return false;

        $grupo = $despesa['grupo_despesa'];
        $numeroParcela = (int) $despesa['numero_parcelas'];

        // === REGRA DE ESCOLHA ===
        $idsParaExcluir = [];

        if ($escopo === 'somente' || !$grupo) {
            // Apenas a despesa atual
            $idsParaExcluir = [$id];
        } else {
            // Coleta todas as despesas do mesmo grupo
            $filtro = ($escopo === 'futuras')
                ? "AND numero_parcelas >= $numeroParcela"
                : ""; // todas = nenhuma restrição

            $sql = "SELECT id_despesa, valor_pago, id_conta, status FROM despesas 
                WHERE grupo_despesa = ? AND id_usuario = ? $filtro";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $grupo, $idUsuario);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $idsParaExcluir[] = (int)$row['id_despesa'];

                // Se tiver conta e estiver pago, devolve saldo
                if (!empty($row['id_conta']) && $row['status'] === 'pago') {
                    $valor = (float) $row['valor_pago'];
                    $idConta = (int) $row['id_conta'];
                    $this->conn->query("
                    UPDATE contas_bancarias
                    SET saldo_atual = saldo_atual + {$valor}
                    WHERE id_conta = {$idConta}
                ");
                }
            }
        }

        // Exclui todas as despesas listadas
        if (empty($idsParaExcluir)) return false;

        $in = implode(',', array_map('intval', $idsParaExcluir));
        return $this->conn->query("DELETE FROM despesas WHERE id_despesa IN ($in)");
    }

    public function estornarFatura(
        int   $idCartao,
        int   $idConta,
        float $valor,
        int   $idUsuario,
        int   $mes,
        int   $ano,
        int   $idCategoria,
        int   $idSubcategoria,
        int   $idForma
    ): bool {
        require_once BASE_PATH . '/app/helpers/logger.php'; // logDespesas

        $this->conn->begin_transaction();
        try {
            // 1) Marca as despesas pagas como “estornado”
            $sql1 = "
    UPDATE despesas
       SET status        = 'estornado',
           data_pagamento = NOW(),
           valor_pago     = 0
     WHERE id_cartao = ?
       AND id_usuario = ?
       AND MONTH(data_vencimento) = ?
       AND YEAR(data_vencimento)  = ?
";
            logDespesas("SQL1: " . $sql1 . " [" . implode(',', [$idCartao, $idUsuario, $mes, $ano]) . "]", 'DEBUG');
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->bind_param("iiii", $idCartao, $idUsuario, $mes, $ano);
            $stmt1->execute();
            logDespesas("stmt1->error: " . $stmt1->error, 'DEBUG');

            // 2) Insere lançamento de estorno (valor negativo)
            $sql2 = "
            INSERT INTO despesas (
                id_usuario,
                id_categoria,
                id_subcategoria,
                descricao,
                valor,
                valor_pago,
                juros,
                desconto,
                id_conta,
                id_cartao,
                id_forma_transacao,
                data_vencimento,
                data_pagamento,
                status,
                observacoes,
                criado_em,
                atualizado_em
            ) VALUES (?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, CURDATE(), NOW(), 'estornado', 'Estorno automático', NOW(), NOW())
        ";
            $descricao = "Estorno fatura {$mes}/{$ano}";
            $valorNeg  = -1 * $valor;
            logDespesas("SQL2: " . $sql2 . " params: [" . implode(',', [
                $idUsuario,
                $idCategoria,
                $idSubcategoria,
                $descricao,
                $valorNeg,
                $valorNeg,
                $idConta,
                $idCartao,
                $idForma
            ]) . "]", 'DEBUG');
            $stmt2 = $this->conn->prepare($sql2);
            // tipos:  i   i      i          s       d       d       i       i       i
            $stmt2->bind_param(
                "iiisddiii",
                $idUsuario,
                $idCategoria,
                $idSubcategoria,
                $descricao,
                $valorNeg,
                $valorNeg,
                $idConta,
                $idCartao,
                $idForma
            );
            $stmt2->execute();
            logDespesas("stmt2->error: " . $stmt2->error, 'DEBUG');

            // 3) Atualiza saldo da conta (crédito aumenta saldo)
            $sql3 = "
            UPDATE contas_bancarias
               SET saldo_atual = saldo_atual + ?
             WHERE id_conta = ?
               AND id_usuario = ?
        ";
            logDespesas("SQL3: " . $sql3 . " [" . implode(',', [$valor, $idConta, $idUsuario]) . "]", 'DEBUG');
            $stmt3 = $this->conn->prepare($sql3);
            $stmt3->bind_param("dii", $valor, $idConta, $idUsuario);
            $stmt3->execute();
            logDespesas("stmt3->error: " . $stmt3->error, 'DEBUG');

            $this->conn->commit();
            logDespesas("estornarFatura: commit realizado com sucesso", 'INFO');
            return true;
        } catch (\Exception $e) {
            $this->conn->rollback();
            logDespesas("estornarFatura: rollback devido a erro - " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
}
