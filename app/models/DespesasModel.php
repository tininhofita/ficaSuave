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
            cb.saldo_atual
        FROM despesas d
        LEFT JOIN categorias c ON d.id_categoria = c.id_categoria
        LEFT JOIN subcategorias s ON d.id_subcategoria = s.id_subcategoria
        LEFT JOIN formas_transacao f ON d.id_forma_transacao = f.id_forma_transacao
        LEFT JOIN contas_bancarias cb ON d.id_conta = cb.id_conta
        WHERE d.id_usuario = $idUsuario
        ORDER BY d.criado_em DESC
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
    public function salvarDespesa(array $dados): bool
    {
        try {
            // normaliza dados básicos
            $descricao = ucwords(strtolower(trim($dados['descricao'] ?? '')));
            $idUsuario = (int)($dados['id_usuario'] ?? 0);
            $valor     = (float)($dados['valor'] ?? 0);

            // calcula valor_pago, juros e desconto
            $valorPago = isset($dados['valor_pago']) ? (float)$dados['valor_pago'] : 0.00;
            $juros     = 0.00;
            $desconto  = 0.00;

            // status seguro + datas
            $statusPermitidos = ['pendente', 'pago', 'atrasado'];
            $status = isset($dados['status']) && in_array($dados['status'], $statusPermitidos, true)
                ? $dados['status'] : 'pendente';

            if ($status === 'pago') {
                if ($valorPago < $valor)       $desconto = $valor - $valorPago;
                elseif ($valorPago > $valor)   $juros    = $valorPago - $valor;
            } else {
                // se não está pago, nada de liquidação
                $valorPago = 0.00;
                $juros = 0.00;
                $desconto = 0.00;
            }

            // data_pagamento: NULL quando não pago (NUNCA string vazia)
            $dataPagamento = ($status === 'pago' && !empty($dados['data_pagamento']))
                ? $dados['data_pagamento']
                : null;

            // flags
            $recorrente = (isset($dados['recorrente']) && (string)$dados['recorrente'] === '1') ? 1 : 0;
            $parcelado  = (isset($dados['parcelado'])  && (string)$dados['parcelado']  === '1') ? 1 : 0;

            // conta e forma
            $idConta = (isset($dados['id_conta']) && $dados['id_conta'] !== '' && is_numeric($dados['id_conta']))
                ? (int)$dados['id_conta'] : null;

            // aceita tanto "id_forma_transacao" quanto "forma" vindo do form
            $idFormaTransacao = isset($dados['id_forma_transacao'])
                ? (int)$dados['id_forma_transacao']
                : (int)($dados['forma'] ?? 1);

            // categoria / sub
            $idCategoria    = (int)($dados['categoria'] ?? 0);
            $idSubcategoria = (int)($dados['subcategoria'] ?? 0);

            // base de data (vencimento é obrigatório)
            $dataVencimento = $dados['data_vencimento'] ?? '';
            if (empty($dataVencimento)) {
                throw new \Exception('Data de vencimento é obrigatória');
            }
            $baseDate = new \DateTime($dataVencimento);
            $dv       = $baseDate->format('Y-m-d');

            // parcelas / grupo
            $totalParcelas  = max(1, (int)($dados['total_parcelas'] ?? 1));
            $numeroParcelas = $parcelado ? max(1, (int)($dados['numero_parcelas'] ?? 1)) : 1;

            if ($parcelado && $totalParcelas > 1) {
                $grupoDespesa = uniqid('grp_', true);
            } elseif ($recorrente && !$parcelado) {
                $grupoDespesa = uniqid('rec_', true);
            } else {
                $grupoDespesa = null; // OK mandar NULL em coluna texto
            }

            $observacoes = $dados['observacoes'] ?? '';

            // prepara o INSERT (20 placeholders)
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
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
        ";

            // sanity: deve ter 20 '?'
            if (substr_count($sql, '?') !== 20) {
                throw new \Exception('Placeholders do INSERT não batem (esperado 20).');
            }

            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new \Exception('Prepare falhou: ' . $this->conn->error);
            }

            // se for parcelado com múltiplas parcelas, a primeira NÃO é a última
            $ultimaParcela = ($parcelado && $totalParcelas > 1 && $numeroParcelas < $totalParcelas) ? 0 : 1;

            // tipos corretos p/ 20 vars
            $types = "iiisddddiisssiiiiiss";

            // 1) principal
            $stmt->bind_param(
                $types,
                $idUsuario,       // i
                $idCategoria,     // i
                $idSubcategoria,  // i
                $descricao,       // s
                $valor,           // d
                $valorPago,       // d
                $juros,           // d
                $desconto,        // d
                $idConta,         // i (NULL ok)
                $idFormaTransacao, // i
                $dv,              // s
                $dataPagamento,   // s (NULL ok!)
                $status,          // s
                $recorrente,      // i
                $parcelado,       // i
                $numeroParcelas,  // i
                $totalParcelas,   // i
                $ultimaParcela,   // i
                $observacoes,     // s
                $grupoDespesa     // s (NULL ok)
            );

            if (!$stmt->execute()) {
                throw new \Exception('Erro ao inserir despesa: ' . $stmt->error);
            }

            // 1.1) Se já foi pago, debita da conta selecionada
            if ($status === 'pago' && $idConta) {
                $upd = $this->conn->prepare("
                UPDATE contas_bancarias
                   SET saldo_atual = saldo_atual - ?
                 WHERE id_conta = ? AND id_usuario = ?
            ");
                if ($upd) {
                    $upd->bind_param("dii", $valorPago, $idConta, $idUsuario);
                    $upd->execute();
                }
            }

            // 2) Parcelas (se parcelado)
            if ($parcelado && $totalParcelas > 1) {
                for ($i = $numeroParcelas + 1; $i <= $totalParcelas; $i++) {
                    $venc   = (clone $baseDate)->modify('+' . ($i - $numeroParcelas) . ' month')->format('Y-m-d');
                    $vpRec  = 0.00;
                    $juRec  = 0.00;
                    $deRec  = 0.00;
                    $dpRec  = null;          // <- NULL (nada de '')
                    $stRec  = 'pendente';
                    $recRec = 0;
                    $parcRec = 1;
                    $numRec = $i;
                    $totRec = $totalParcelas;
                    $ultRec = ($i === $totalParcelas) ? 1 : 0;
                    $obsRec = $observacoes;
                    $grpRec = $grupoDespesa;

                    $stmt->bind_param(
                        $types,
                        $idUsuario,
                        $idCategoria,
                        $idSubcategoria,
                        $descricao,
                        $valor,
                        $vpRec,
                        $juRec,
                        $deRec,
                        $idConta,
                        $idFormaTransacao,
                        $venc,
                        $dpRec,     // NULL ok
                        $stRec,
                        $recRec,
                        $parcRec,
                        $numRec,
                        $totRec,
                        $ultRec,
                        $obsRec,
                        $grpRec
                    );
                    if (!$stmt->execute()) {
                        throw new \Exception('Erro ao inserir parcela: ' . $stmt->error);
                    }
                }
            }

            // 3) Recorrências (se recorrente e não parcelado)
            if ($recorrente && !$parcelado) {
                for ($i = 1; $i < 12; $i++) {
                    $venc   = (clone $baseDate)->modify("+{$i} month")->format('Y-m-d');
                    $vpR    = 0.00;
                    $juR    = 0.00;
                    $deR    = 0.00;
                    $dpR    = null;          // <- NULL
                    $stR    = 'pendente';
                    $recR   = 1;
                    $parcR  = 0;
                    $numR   = 1;
                    $totR   = 1;
                    $ultR   = 1;
                    $obsR   = $observacoes;
                    $grpR   = $grupoDespesa;

                    $stmt->bind_param(
                        $types,
                        $idUsuario,
                        $idCategoria,
                        $idSubcategoria,
                        $descricao,
                        $valor,
                        $vpR,
                        $juR,
                        $deR,
                        $idConta,
                        $idFormaTransacao,
                        $venc,
                        $dpR,      // NULL ok
                        $stR,
                        $recR,
                        $parcR,
                        $numR,
                        $totR,
                        $ultR,
                        $obsR,
                        $grpR
                    );
                    if (!$stmt->execute()) {
                        throw new \Exception('Erro ao inserir recorrência: ' . $stmt->error);
                    }
                }
            }

            return true;
        } catch (\Throwable $e) {
            error_log('[DespesasModel::salvarDespesa] ' . $e->getMessage());
            return false;
        }
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
            $stmtCredito = $this->conn->prepare("
                UPDATE contas_bancarias
                SET saldo_atual = saldo_atual + ?
                WHERE id_conta = ? AND id_usuario = ?
            ");
            $stmtCredito->bind_param("dii", $original['valor_pago'], $original['id_conta'], $idUsuario);
            $stmtCredito->execute();
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
                "iisddddiisssiiiiisii",
                $categoria,
                $subcategoria,
                $descricao,
                $valor,
                $valorPago,
                $juros,
                $desconto,
                $idConta,
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
                    $stmtDebito = $this->conn->prepare("
                        UPDATE contas_bancarias
                        SET saldo_atual = saldo_atual - ?
                        WHERE id_conta = ? AND id_usuario = ?
                    ");
                    $stmtDebito->bind_param("dii", $valorPago, $idConta, $idUsuario);
                    $stmtDebito->execute();
                }
                // 9b) se já estava pago e mudou o valor, ajusta diferença
                elseif (
                    $original['status'] === 'pago'
                    && $status === 'pago'
                    && $valorPago !== (float)$original['valor_pago']
                ) {
                    $delta = $valorPago - (float)$original['valor_pago'];
                    $stmtAjuste = $this->conn->prepare("
                        UPDATE contas_bancarias
                        SET saldo_atual = saldo_atual - ?
                        WHERE id_conta = ? AND id_usuario = ?
                    ");
                    $stmtAjuste->bind_param("dii", $delta, $idConta, $idUsuario);
                    $stmtAjuste->execute();
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
        try {
            $this->conn->begin_transaction();

            // 1) Busca a despesa garantindo o usuário dono dela
            $sql = "SELECT valor, status, id_conta, id_forma_transacao, descricao
                FROM despesas 
                WHERE id_despesa = ? AND id_usuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $idDespesa, $idUsuario);
            $stmt->execute();
            $despesa = $stmt->get_result()->fetch_assoc();

            if (!$despesa) {
                throw new \Exception('Despesa não encontrada ou não pertence ao usuário');
            }

            if ($despesa['status'] === 'pago') {
                throw new \Exception('Despesa já está paga');
            }

            // 2) Impede pagamento de despesas de cartão (devem ser pagas via fatura)
            $stmtF = $this->conn->prepare("SELECT nome FROM formas_transacao WHERE id_forma_transacao = ?");
            $stmtF->bind_param("i", $despesa['id_forma_transacao']);
            $stmtF->execute();
            $nomeForma = $stmtF->get_result()->fetch_assoc()['nome'] ?? '';
            if (strtolower($nomeForma) === 'cartão de crédito') {
                throw new \Exception('Despesas de cartão devem ser pagas via fatura');
            }

            // 3) Busca saldo da conta do usuário
            $stmtC = $this->conn->prepare(
                "SELECT saldo_atual, nome_conta FROM contas_bancarias 
             WHERE id_conta = ? AND id_usuario = ?"
            );
            $stmtC->bind_param("ii", $idConta, $idUsuario);
            $stmtC->execute();
            $conta = $stmtC->get_result()->fetch_assoc();

            if (!$conta) {
                throw new \Exception('Conta bancária não encontrada');
            }

            $saldoAtual = (float)$conta['saldo_atual'];

            // 4) Calcula juros/desconto
            $valorOriginal = (float)$despesa['valor'];
            $juros = $desconto = 0.00;
            if ($valor < $valorOriginal) {
                $desconto = $valorOriginal - $valor;
            } elseif ($valor > $valorOriginal) {
                $juros = $valor - $valorOriginal;
            }


            // 5) Atualiza a despesa
            $upd = $this->conn->prepare("
            UPDATE despesas
            SET status = 'pago',
                data_pagamento = ?,
                valor_pago     = ?,
                id_conta       = ?,
                desconto       = ?,
                juros          = ?,
                atualizado_em  = NOW()
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
                throw new \Exception('Erro ao atualizar despesa: ' . $upd->error);
            }

            // 6) Debita saldo na conta do usuário (usando prepared statement)
            $stmtDebito = $this->conn->prepare("
            UPDATE contas_bancarias
            SET saldo_atual = saldo_atual - ?
            WHERE id_conta = ? AND id_usuario = ?
        ");
            $stmtDebito->bind_param("dii", $valor, $idConta, $idUsuario);

            if (!$stmtDebito->execute()) {
                throw new \Exception('Erro ao debitar conta bancária: ' . $stmtDebito->error);
            }

            $this->conn->commit();

            return true;
        } catch (\Throwable $e) {
            $this->conn->rollback();
            error_log('[DespesasModel::confirmarPagamento] ' . $e->getMessage());
            return false;
        }
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

        if (!$despesa) {
            return false;
        }

        $grupo = $despesa['grupo_despesa'];
        $numeroParcela = (int) $despesa['numero_parcelas'];

        // === REGRA DE ESCOLHA ===
        $idsParaExcluir = [];

        if ($escopo === 'somente' || !$grupo) {
            // Apenas a despesa atual
            $idsParaExcluir = [$id];

            // Se tiver conta e estiver pago, devolve saldo
            if (!empty($despesa['id_conta']) && $despesa['status'] === 'pago') {
                $valor = (float) $despesa['valor_pago'];
                $idConta = (int) $despesa['id_conta'];

                $stmtCredito = $this->conn->prepare("
                    UPDATE contas_bancarias
                    SET saldo_atual = saldo_atual + ?
                    WHERE id_conta = ? AND id_usuario = ?
                ");
                $stmtCredito->bind_param("dii", $valor, $idConta, $idUsuario);
                $stmtCredito->execute();
            }
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

                    $stmtCredito = $this->conn->prepare("
                        UPDATE contas_bancarias
                        SET saldo_atual = saldo_atual + ?
                        WHERE id_conta = ? AND id_usuario = ?
                    ");
                    $stmtCredito->bind_param("dii", $valor, $idConta, $idUsuario);
                    $stmtCredito->execute();
                }
            }
        }

        // Exclui todas as despesas listadas
        if (empty($idsParaExcluir)) {
            return false;
        }

        $in = implode(',', array_map('intval', $idsParaExcluir));
        return $this->conn->query("DELETE FROM despesas WHERE id_despesa IN ($in)");
    }
}
