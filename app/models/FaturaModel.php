<?php
require_once BASE_PATH . '/app/config/db_config.php';

class FaturaModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDatabase();
    }

    public function buscarTodas($idUsuario): array
    {
        $idUsuario = (int)$idUsuario;

        $sql = "
        SELECT 
            f.id_fatura,
            f.grupo,
            f.id_usuario,
            f.id_categoria,
            f.id_subcategoria,
            f.id_forma_transacao,
            f.id_conta,
            f.id_cartao,
            f.descricao,
            f.valor,
            f.valor_pago,
            f.juros,
            f.desconto,
            f.data_vencimento,
            f.data_pagamento,
            f.status,
            f.recorrente,
            f.parcelado,
            f.numero_parcelas,
            f.total_parcelas,
            f.ultima_parcela,
            f.observacoes,
            f.criado_em,
            f.atualizado_em,
            c.nome_categoria,
            s.nome_subcategoria,
            ft.nome as nome_forma_transacao,
            cb.nome_conta,
            cart.nome_cartao
        FROM faturas f
        LEFT JOIN categorias c ON f.id_categoria = c.id_categoria
        LEFT JOIN subcategorias s ON f.id_subcategoria = s.id_subcategoria
        LEFT JOIN formas_transacao ft ON f.id_forma_transacao = ft.id_forma_transacao
        LEFT JOIN contas_bancarias cb ON f.id_conta = cb.id_conta
        LEFT JOIN cartoes cart ON f.id_cartao = cart.id_cartao
        WHERE f.id_usuario = $idUsuario
        ORDER BY f.criado_em ASC
        ";

        $result = $this->conn->query($sql);

        $faturas = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $faturas[] = $row;
            }
        }

        return $faturas;
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

        $sql = "SELECT s.id_subcategoria, s.nome_subcategoria, s.id_categoria
        FROM subcategorias s
        INNER JOIN categorias c ON s.id_categoria = c.id_categoria
        WHERE s.ativa = 1 
        AND c.tipo = 'despesa'
        AND (s.id_usuario = $idUsuario or s.subcategoria_padrao = 1)
        ORDER BY s.nome_subcategoria ASC";

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
        $sql = "SELECT f.*, c.nome_categoria, s.nome_subcategoria
            FROM faturas f
            LEFT JOIN categorias c ON f.id_categoria = c.id_categoria
            LEFT JOIN subcategorias s ON f.id_subcategoria = s.id_subcategoria
            WHERE f.id_usuario = ? AND f.id_cartao = ?
            AND MONTH(f.data_vencimento) = ? AND YEAR(f.data_vencimento) = ?
            ORDER BY f.criado_em DESC";

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



    // aqui vai precisar inserir mais comandos acima. 


    public function salvarDespesaFatura(array $dados): bool
    {
        try {
            $this->conn->begin_transaction();

            // --- Helper p/ bind com checagem forte ---
            $bindAll = function (mysqli_stmt $stmt, string $types, array &$vars) {
                if (strlen($types) !== count($vars)) {
                    throw new \Exception(
                        "bind_param mismatch: types=" . strlen($types) . " vars=" . count($vars)
                    );
                }
                // chama bind_param com referências
                $ok = $stmt->bind_param($types, ...$vars);
                if (!$ok) {
                    throw new \Exception("bind_param falhou: " . $stmt->error);
                }
            };

            // --- Normalizações / sanitizações ---
            $descricao = ucwords(strtolower(trim($dados['descricao'] ?? '')));
            $idUsuario = (int)($dados['id_usuario'] ?? 0);
            $valor     = (float)($dados['valor'] ?? 0);

            // status (enum seguro)
            $permitidos = ['pendente', 'pago', 'atrasado'];
            $status = isset($dados['status']) && in_array($dados['status'], $permitidos, true)
                ? $dados['status'] : 'pendente';

            // valores de liquidação
            $valorPago = isset($dados['valor_pago']) ? (float)$dados['valor_pago'] : 0.00;
            $juros     = 0.00;
            $desconto  = 0.00;
            if ($status === 'pago') {
                if ($valorPago < $valor) $desconto = $valor - $valorPago;
                elseif ($valorPago > $valor) $juros = $valorPago - $valor;
            } else {
                $valorPago = 0.00;
                $juros = 0.00;
                $desconto = 0.00;
            }

            // datas
            $dataPagamento = ($status === 'pago' && !empty($dados['data_pagamento'])) ? $dados['data_pagamento'] : null;

            // flags
            $recorrente = (isset($dados['recorrente']) && (string)$dados['recorrente'] === '1') ? 1 : 0;
            $parcelado  = (isset($dados['parcelado'])  && (string)$dados['parcelado']  === '1') ? 1 : 0;

            // relacionamentos
            $idConta          = (isset($dados['id_conta'])  && $dados['id_conta']  !== '' && is_numeric($dados['id_conta']))  ? (int)$dados['id_conta']  : null;
            $idCartao         = (isset($dados['id_cartao']) && $dados['id_cartao'] !== '' && is_numeric($dados['id_cartao'])) ? (int)$dados['id_cartao'] : null;
            $idFormaTransacao = $idCartao ? 2 : (int)($dados['id_forma_transacao'] ?? 1);

            // categoria/sub
            $categoria    = (int)($dados['categoria'] ?? 0);
            $subcategoria = (int)($dados['subcategoria'] ?? 0);

            // parcelas / grupo
            $totalParcelas  = max(1, (int)($dados['total_parcelas'] ?? 1));
            $numeroParcelas = $parcelado ? max(1, (int)($dados['numero_parcelas'] ?? 1)) : 1;

            if ($parcelado && $totalParcelas > 1)       $grupoDespesa = uniqid('grp_', true);
            elseif ($recorrente && !$parcelado)         $grupoDespesa = uniqid('rec_', true);
            else                                        $grupoDespesa = null;

            // vencimento base
            $baseDate = new \DateTime($dados['data_vencimento']);
            $dv       = $baseDate->format('Y-m-d');

            // texto livre
            $observacoes = $dados['observacoes'] ?? '';

            // SQL base (21 placeholders)
            $sql = "
            INSERT INTO faturas (
                grupo,
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
                criado_em,
                atualizado_em
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
        ";

            // sanity quick-check
            if (substr_count($sql, '?') !== 21) {
                throw new \Exception("Sanity check: placeholders != 21");
            }

            $types = "siiisddddiiisssiiiiis"; // 21 letras

            // ===== 1) Lançamento principal =====
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) throw new \Exception('Prepare (principal) falhou: ' . $this->conn->error);

            $ultimaParcela = ($parcelado && $totalParcelas > 1 && $numeroParcelas < $totalParcelas) ? 0 : 1;

            $vars1 = [
                &$grupoDespesa,   // s
                &$idUsuario,      // i
                &$categoria,      // i
                &$subcategoria,   // i
                &$descricao,      // s
                &$valor,          // d
                &$valorPago,      // d
                &$juros,          // d
                &$desconto,       // d
                &$idConta,        // i (null ok)
                &$idCartao,       // i (null ok)
                &$idFormaTransacao, // i
                &$dv,             // s
                &$dataPagamento,  // s (null ok)
                &$status,         // s
                &$recorrente,     // i
                &$parcelado,      // i
                &$numeroParcelas, // i
                &$totalParcelas,  // i
                &$ultimaParcela,  // i
                &$observacoes     // s
            ];
            $bindAll($stmt, $types, $vars1);

            if (!$stmt->execute()) {
                throw new \Exception('Erro ao inserir despesa principal: ' . $stmt->error);
            }

            // 1.1) Debita conta se já pago
            if ($status === 'pago' && $idConta) {
                $stmtDeb = $this->conn->prepare("
                UPDATE contas_bancarias
                   SET saldo_atual = saldo_atual - ?
                 WHERE id_conta = ? AND id_usuario = ?
            ");
                if (!$stmtDeb) throw new \Exception('Prepare (debito) falhou: ' . $this->conn->error);
                $stmtDeb->bind_param("dii", $valorPago, $idConta, $idUsuario);
                if (!$stmtDeb->execute()) {
                    throw new \Exception('Erro ao debitar conta: ' . $stmtDeb->error);
                }
            }

            // ===== 2) Parcelas =====
            if ($parcelado && $totalParcelas > 1) {
                for ($i = $numeroParcelas + 1; $i <= $totalParcelas; $i++) {
                    $venc = (clone $baseDate)->modify('+' . ($i - $numeroParcelas) . ' month')->format('Y-m-d');
                    $ultRec = ($i === $totalParcelas) ? 1 : 0;

                    $stmtParcela = $this->conn->prepare($sql);
                    if (!$stmtParcela) throw new \Exception('Prepare (parcela) falhou: ' . $this->conn->error);

                    $vpRec = 0.00;
                    $juRec = 0.00;
                    $deRec = 0.00;
                    $dpRec = null;
                    $stRec = 'pendente';
                    $recRec = 0;
                    $parcRec = 1;
                    $obsRec = $observacoes;

                    $vars2 = [
                        &$grupoDespesa,
                        &$idUsuario,
                        &$categoria,
                        &$subcategoria,
                        &$descricao,
                        &$valor,
                        &$vpRec,
                        &$juRec,
                        &$deRec,
                        &$idConta,
                        &$idCartao,
                        &$idFormaTransacao,
                        &$venc,
                        &$dpRec,
                        &$stRec,
                        &$recRec,
                        &$parcRec,
                        &$i,
                        &$totalParcelas,
                        &$ultRec,
                        &$obsRec
                    ];
                    $bindAll($stmtParcela, $types, $vars2);

                    if (!$stmtParcela->execute()) {
                        throw new \Exception('Erro ao inserir parcela: ' . $stmtParcela->error);
                    }
                }
            }

            // ===== 3) Recorrências (12 futuras) =====
            if ($recorrente && !$parcelado) {
                for ($i = 1; $i < 12; $i++) {
                    $venc = (clone $baseDate)->modify("+{$i} month")->format('Y-m-d');

                    $stmtRec = $this->conn->prepare($sql);
                    if (!$stmtRec) throw new \Exception('Prepare (recorrência) falhou: ' . $this->conn->error);

                    $vp2 = 0.00;
                    $ju2 = 0.00;
                    $de2 = 0.00;
                    $dp2 = null;
                    $st2 = 'pendente';
                    $rec2 = 1;
                    $parc2 = 0;
                    $num2 = 1;
                    $tot2 = 1;
                    $ult2 = 1;
                    $obs2 = $observacoes;

                    $vars3 = [
                        &$grupoDespesa,
                        &$idUsuario,
                        &$categoria,
                        &$subcategoria,
                        &$descricao,
                        &$valor,
                        &$vp2,
                        &$ju2,
                        &$de2,
                        &$idConta,
                        &$idCartao,
                        &$idFormaTransacao,
                        &$venc,
                        &$dp2,
                        &$st2,
                        &$rec2,
                        &$parc2,
                        &$num2,
                        &$tot2,
                        &$ult2,
                        &$obs2
                    ];
                    $bindAll($stmtRec, $types, $vars3);

                    if (!$stmtRec->execute()) {
                        throw new \Exception('Erro ao inserir recorrência: ' . $stmtRec->error);
                    }
                }
            }

            $this->conn->commit();
            return true;
        } catch (\Throwable $e) {
            $this->conn->rollback();
            error_log("Erro ao salvar despesa na fatura: " . $e->getMessage());
            return false;
        }
    }

    public function excluirDespesaFatura($idUsuario, $idFatura, $escopo = 'somente'): bool
    {
        try {
            $this->conn->begin_transaction();

            // Busca a despesa para verificar se é do usuário
            $stmt = $this->conn->prepare("SELECT * FROM faturas WHERE id_fatura = ? AND id_usuario = ?");
            $stmt->bind_param("ii", $idFatura, $idUsuario);
            $stmt->execute();
            $despesa = $stmt->get_result()->fetch_assoc();

            if (!$despesa) {
                throw new \Exception('Despesa não encontrada ou não pertence ao usuário');
            }

            // Se foi pago, reverte o débito da conta
            if ($despesa['status'] === 'pago' && $despesa['id_conta'] && $despesa['valor_pago'] > 0) {
                $stmtDebito = $this->conn->prepare("
                    UPDATE contas_bancarias 
                    SET saldo_atual = saldo_atual + ? 
                    WHERE id_conta = ? AND id_usuario = ?
                ");
                $stmtDebito->bind_param("dii", $despesa['valor_pago'], $despesa['id_conta'], $idUsuario);
                if (!$stmtDebito->execute()) {
                    throw new \Exception('Erro ao reverter débito da conta');
                }
            }

            switch ($escopo) {
                case 'somente':
                    // Exclui apenas esta despesa
                    $stmt = $this->conn->prepare("DELETE FROM faturas WHERE id_fatura = ? AND id_usuario = ?");
                    $stmt->bind_param("ii", $idFatura, $idUsuario);
                    break;

                case 'futuras':
                    // Exclui esta e todas as futuras do grupo
                    if ($despesa['grupo']) {
                        $stmt = $this->conn->prepare("
                            DELETE FROM faturas 
                            WHERE grupo = ? AND id_usuario = ? 
                            AND data_vencimento > ?
                        ");
                        $stmt->bind_param("sis", $despesa['grupo'], $idUsuario, $despesa['data_vencimento']);
                    } else {
                        // Se não tem grupo, exclui apenas esta
                        $stmt = $this->conn->prepare("DELETE FROM faturas WHERE id_fatura = ? AND id_usuario = ?");
                        $stmt->bind_param("ii", $idFatura, $idUsuario);
                    }
                    break;

                case 'todas':
                    // Exclui todas as despesas do grupo
                    if ($despesa['grupo']) {
                        $stmt = $this->conn->prepare("DELETE FROM faturas WHERE grupo = ? AND id_usuario = ?");
                        $stmt->bind_param("si", $despesa['grupo'], $idUsuario);
                    } else {
                        // Se não tem grupo, exclui apenas esta
                        $stmt = $this->conn->prepare("DELETE FROM faturas WHERE id_fatura = ? AND id_usuario = ?");
                        $stmt->bind_param("ii", $idFatura, $idUsuario);
                    }
                    break;

                default:
                    throw new \Exception('Escopo inválido');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Erro ao excluir despesa: ' . $stmt->error);
            }

            $this->conn->commit();
            return true;
        } catch (\Throwable $e) {
            $this->conn->rollback();
            error_log("Erro ao excluir despesa da fatura: " . $e->getMessage());
            return false;
        }
    }

    public function atualizarDespesaFatura(array $dados): bool
    {
        try {
            $this->conn->begin_transaction();


            $idFatura = (int)($dados['id'] ?? $dados['id_despesa'] ?? 0);
            $idUsuario = (int)$dados['id_usuario'];

            // Busca a despesa original
            $stmt = $this->conn->prepare("SELECT * FROM faturas WHERE id_fatura = ? AND id_usuario = ?");
            $stmt->bind_param("ii", $idFatura, $idUsuario);
            $stmt->execute();
            $original = $stmt->get_result()->fetch_assoc();

            if (!$original) {
                throw new \Exception('Despesa não encontrada ou não pertence ao usuário');
            }

            // Normaliza dados
            $descricao = ucwords(strtolower(trim($dados['descricao'] ?? '')));
            $valor = (float)($dados['valor'] ?? 0);
            $valorPago = isset($dados['valor_pago']) ? (float)$dados['valor_pago'] : 0.00;
            $status = $dados['status'] ?? 'pendente';

            // Calcula juros e desconto
            $juros = 0.00;
            $desconto = 0.00;
            if ($status === 'pago') {
                if ($valorPago < $valor) {
                    $desconto = $valor - $valorPago;
                } elseif ($valorPago > $valor) {
                    $juros = $valorPago - $valor;
                }
            } else {
                $valorPago = 0.00;
                $juros = 0.00;
                $desconto = 0.00;
            }

            $dataPagamento = ($status === 'pago' && !empty($dados['data_pagamento']))
                ? $dados['data_pagamento']
                : null;

            $idConta = (isset($dados['id_conta']) && $dados['id_conta'] !== '' && is_numeric($dados['id_conta']))
                ? (int)$dados['id_conta']
                : null;
            $idCartao = (isset($dados['id_cartao']) && $dados['id_cartao'] !== '' && is_numeric($dados['id_cartao']))
                ? (int)$dados['id_cartao']
                : null;
            $idFormaTransacao = $idCartao ? 2 : (int)($dados['id_forma_transacao'] ?? 1);

            $categoria = (int)($dados['categoria'] ?? 0);
            $subcategoria = (int)($dados['subcategoria'] ?? 0);
            $observacoes = $dados['observacoes'] ?? '';

            // Se passou de pago → não pago, reverte o débito
            if ($original['status'] === 'pago' && $status !== 'pago' && $original['id_conta']) {
                $stmtRev = $this->conn->prepare("
                    UPDATE contas_bancarias 
                    SET saldo_atual = saldo_atual + ? 
                    WHERE id_conta = ? AND id_usuario = ?
                ");
                $stmtRev->bind_param("dii", $original['valor_pago'], $original['id_conta'], $idUsuario);
                if (!$stmtRev->execute()) {
                    throw new \Exception('Erro ao reverter débito da conta');
                }
            }

            // Atualiza a despesa
            $stmt = $this->conn->prepare("
                UPDATE faturas SET
                    id_categoria = ?,
                    id_subcategoria = ?,
                    descricao = ?,
                    valor = ?,
                    valor_pago = ?,
                    juros = ?,
                    desconto = ?,
                    id_conta = ?,
                    id_cartao = ?,
                    id_forma_transacao = ?,
                    data_vencimento = ?,
                    data_pagamento = ?,
                    status = ?,
                    observacoes = ?,
                    atualizado_em = NOW()
                WHERE id_fatura = ? AND id_usuario = ?
            ");

            $stmt->bind_param(
                "iisddddiiissssii",
                $categoria,
                $subcategoria,
                $descricao,
                $valor,
                $valorPago,
                $juros,
                $desconto,
                $idConta,
                $idCartao,
                $idFormaTransacao,
                $dados['data_vencimento'],
                $dataPagamento,
                $status,
                $observacoes,
                $idFatura,
                $idUsuario
            );

            if (!$stmt->execute()) {
                throw new \Exception('Erro ao atualizar despesa: ' . $stmt->error);
            }

            // Se passou de não pago → pago, debita da conta
            if ($original['status'] !== 'pago' && $status === 'pago' && $idConta) {
                $stmtDeb = $this->conn->prepare("
                    UPDATE contas_bancarias 
                    SET saldo_atual = saldo_atual - ? 
                    WHERE id_conta = ? AND id_usuario = ?
                ");
                $stmtDeb->bind_param("dii", $valorPago, $idConta, $idUsuario);
                if (!$stmtDeb->execute()) {
                    throw new \Exception('Erro ao debitar conta');
                }
            }

            // Se já estava pago e mudou o valor, ajusta diferença
            elseif ($original['status'] === 'pago' && $status === 'pago' && $original['id_conta']) {
                $diferenca = $valorPago - (float)$original['valor_pago'];
                if ($diferenca != 0) {
                    $stmtAjuste = $this->conn->prepare("
                        UPDATE contas_bancarias 
                        SET saldo_atual = saldo_atual - ? 
                        WHERE id_conta = ? AND id_usuario = ?
                    ");
                    $stmtAjuste->bind_param("dii", $diferenca, $original['id_conta'], $idUsuario);
                    if (!$stmtAjuste->execute()) {
                        throw new \Exception('Erro ao ajustar conta');
                    }
                }
            }

            $this->conn->commit();
            return true;
        } catch (\Throwable $e) {
            $this->conn->rollback();
            error_log("Erro ao atualizar despesa da fatura: " . $e->getMessage());
            return false;
        }
    }

    public function pagarFaturaCompleta(array $dados): array
    {
        try {
            $this->conn->begin_transaction();

            $idCartao = (int)($dados['id_cartao'] ?? 0);
            $idUsuario = (int)$dados['id_usuario'];
            $idConta = (int)($dados['id_conta'] ?? 0);
            $dataPagamento = $dados['data_pagamento'] ?? date('Y-m-d');
            $mes = $dados['mes'] ?? date('m');
            $ano = $dados['ano'] ?? date('Y');

            if (!$idCartao || !$idUsuario || !$idConta) {
                throw new \Exception('Dados obrigatórios não fornecidos');
            }

            // Busca todas as despesas pendentes da fatura do cartão no mês/ano
            $stmt = $this->conn->prepare("
                   SELECT f.*, c.nome_conta
                   FROM faturas f
                   LEFT JOIN contas_bancarias c ON f.id_conta = c.id_conta
                   WHERE f.id_cartao = ? 
                   AND f.id_usuario = ?
                   AND MONTH(f.data_vencimento) = ?
                   AND YEAR(f.data_vencimento) = ?
                   AND f.status = 'pendente'
               ");
            $stmt->bind_param("iiii", $idCartao, $idUsuario, $mes, $ano);
            $stmt->execute();
            $despesas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            if (empty($despesas)) {
                throw new \Exception('Nenhuma despesa pendente encontrada para este cartão no período');
            }

            $totalPago = 0;
            $despesasAtualizadas = 0;

            // Atualiza cada despesa para status 'pago'
            foreach ($despesas as $despesa) {
                $stmtUpdate = $this->conn->prepare("
                       UPDATE faturas SET
                           status = 'pago',
                           valor_pago = valor,
                           data_pagamento = ?,
                           id_conta = ?,
                           atualizado_em = NOW()
                       WHERE id_fatura = ? AND id_usuario = ?
                   ");
                $stmtUpdate->bind_param("siii", $dataPagamento, $idConta, $despesa['id_fatura'], $idUsuario);

                if (!$stmtUpdate->execute()) {
                    throw new \Exception('Erro ao atualizar despesa: ' . $stmtUpdate->error);
                }

                $totalPago += (float)$despesa['valor'];
                $despesasAtualizadas++;
            }

            // Debita o valor total da conta bancária
            $stmtDebito = $this->conn->prepare("
                   UPDATE contas_bancarias 
                   SET saldo_atual = saldo_atual - ? 
                   WHERE id_conta = ? AND id_usuario = ?
               ");
            $stmtDebito->bind_param("dii", $totalPago, $idConta, $idUsuario);

            if (!$stmtDebito->execute()) {
                throw new \Exception('Erro ao debitar conta bancária');
            }

            // Verifica se o saldo ficou negativo e registra aviso
            $stmtSaldo = $this->conn->prepare("
                   SELECT saldo_atual FROM contas_bancarias 
                   WHERE id_conta = ? AND id_usuario = ?
               ");
            $stmtSaldo->bind_param("ii", $idConta, $idUsuario);
            $stmtSaldo->execute();
            $saldo = $stmtSaldo->get_result()->fetch_assoc()['saldo_atual'];

            $saldoNegativo = $saldo < 0;
            if ($saldoNegativo) {
                error_log("AVISO: Conta bancária ficou com saldo negativo após pagamento. Saldo atual: R$ " . number_format($saldo, 2, ',', '.'));
            }

            $this->conn->commit();

            error_log("Fatura paga: Cartão $idCartao, $despesasAtualizadas despesas, Total: R$ " . number_format($totalPago, 2, ',', '.'));

            return [
                'sucesso' => true,
                'saldo_negativo' => $saldoNegativo,
                'saldo_atual' => $saldo,
                'total_pago' => $totalPago,
                'despesas_atualizadas' => $despesasAtualizadas
            ];
        } catch (\Throwable $e) {
            $this->conn->rollback();
            error_log("Erro ao pagar fatura: " . $e->getMessage());
            return [
                'sucesso' => false,
                'erro' => $e->getMessage()
            ];
        }
    }
}
