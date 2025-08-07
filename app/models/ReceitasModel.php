<?php
require_once BASE_PATH . '/app/config/db_config.php';

class ReceitasModel
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
        r.*,
        c.nome_categoria,
        s.nome_subcategoria,
        f.nome AS nome_forma,
        b.nome_conta
    FROM receitas r
        LEFT JOIN categorias c ON r.id_categoria = c.id_categoria
        LEFT JOIN subcategorias s ON r.id_subcategoria = s.id_subcategoria
        LEFT JOIN formas_transacao f ON r.id_forma_transacao = f.id_forma_transacao
        LEFT JOIN contas_bancarias b ON r.id_conta = b.id_conta
        WHERE r.id_usuario = $idUsuario
        ORDER BY r.data_vencimento ASC
    ";

        $result = $this->conn->query($sql);

        $receitas = [];
        while ($row = $result->fetch_assoc()) {
            $receitas[] = $row;
        }

        return $receitas;
    }

    public function buscarCategoriasReceita(int $idUsuario): array
    {
        $sql = "
        SELECT 
            id_categoria,
            nome_categoria
        FROM categorias
        WHERE tipo      = 'receita'
          AND ativa     = 1
          AND (
                id_usuario        = ?
             OR categoria_padrao = 1
          )
        ORDER BY nome_categoria
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();

        $cats = [];
        while ($row = $res->fetch_assoc()) {
            $cats[] = $row;
        }
        return $cats;
    }

    public function buscarSubcategoriasReceita(int $idUsuario): array
    {
        $sql = "
            SELECT s.*
            FROM subcategorias s
            INNER JOIN categorias c
                ON s.id_categoria = c.id_categoria
            WHERE c.tipo = 'receita'
              AND c.ativa = 1
              AND (c.id_usuario = ? OR c.categoria_padrao = 1)
            ORDER BY s.nome_subcategoria
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();

        $subs = [];
        while ($row = $res->fetch_assoc()) {
            $subs[] = $row;
        }
        return $subs;
    }

    public function buscarFormasTransacaoReceita($idUsuario): array
    {
        $idUsuario = (int)$idUsuario;

        $sql = "SELECT id_forma_transacao, nome, uso
FROM formas_transacao 
WHERE uso IN ('ambos')
AND ativa = 1
AND id_forma_transacao != 2
AND (id_usuario = $idUsuario OR padrao = 1)
ORDER BY nome ASC";


        $result = $this->conn->query($sql);

        $formasReceita = [];
        while ($row = $result->fetch_assoc()) {
            $formasReceita[] = $row;
        }

        return $formasReceita;
    }

    public function salvarReceita(array $dados): bool
    {

        // Normaliza dados básicos
        $idUsuario       = (int) $dados['id_usuario'];
        $descricao       = ucwords(strtolower(trim($dados['descricao'])));
        $valorLimpo = str_replace('.', '', $dados['valor']);
        $valor      = (float) str_replace(',', '.', $valorLimpo);
        $status          = $dados['status'];
        $valorRecebido = 0.00;
        if ($status === 'recebido' && !empty($dados['valor_recebido'])) {
            // Remove pontos de milhar antes de converter
            $vrLimpo       = str_replace('.', '', $dados['valor_recebido']);
            $valorRecebido = (float) str_replace(',', '.', $vrLimpo);
        }
        $dataRecebimento = ($status === 'recebido' && !empty($dados['data_recebimento']))
            ? $dados['data_recebimento']
            : null;

        // Checkboxes agora vêm como '1' ou nada
        $recorrente = !empty($dados['recorrente']) ? 1 : 0;
        $parcelado  = !empty($dados['parcelado'])  ? 1 : 0;

        // Mapeia selects
        $idCategoria      = !empty($dados['id_categoria'])    ? (int) $dados['id_categoria']    : null;
        $idSubcategoria   = !empty($dados['id_subcategoria']) ? (int) $dados['id_subcategoria'] : null;
        $idConta          = !empty($dados['id_conta'])        ? (int) $dados['id_conta']        : null;
        $idFormaTransacao = !empty($dados['id_forma_transacao'])        ? (int) $dados['id_forma_transacao']        : null;

        // **CALCULA JUROS E DESCONTO AUTOMÁTICO**
        if ($status === 'recebido') {
            if ($valorRecebido > $valor) {
                $juros    = $valorRecebido - $valor;
                $desconto = 0.00;
            } elseif ($valorRecebido < $valor) {
                $desconto = $valor - $valorRecebido;
                $juros    = 0.00;
            } else {
                $juros    = 0.00;
                $desconto = 0.00;
            }
        } else {
            $juros    = 0.00;
            $desconto = 0.00;
        }

        $dataVencimento  = !empty($dados['data_vencimento'])
            ? $dados['data_vencimento']
            : null;
        $observacoes     = $dados['observacoes'] ?? '';

        // Parcelas
        $numeroParcelas = $parcelado
            ? max(1, (int) ($dados['numero_parcelas'] ?? 1))
            : 1;
        $totalParcelas  = $parcelado
            ? max(1, (int) ($dados['total_parcelas'] ?? 1))
            : 1;
        $ultimaParcela  = ($parcelado && $numeroParcelas < $totalParcelas) ? 0 : 1;

        // Gera grupo de parcelado/recorrência
        if ($parcelado && $totalParcelas > 1) {
            $grupoReceita = uniqid('grp_');
        } elseif ($recorrente && ! $parcelado) {
            $grupoReceita = uniqid('rec_');
        } else {
            $grupoReceita = null;
        }

        // Prepara o INSERT principal
        $sql = "
        INSERT INTO receitas (
            id_usuario,
            id_categoria,
            id_subcategoria,
            descricao,
            valor,
            valor_recebido,
            juros,
            desconto,
            id_conta,
            id_forma_transacao,
            data_vencimento,
            data_recebimento,
            status,
            recorrente,
            parcelado,
            numero_parcelas,
            total_parcelas,
            ultima_parcela,
            observacoes,
            grupo_receita,
            criado_em,
            atualizado_em
        ) VALUES (
            ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW(), NOW()
        )
    ";
        $stmt = $this->conn->prepare($sql);

        // Faz bind dos parâmetros (agora todas variáveis)
        $stmt->bind_param(
            'iiisddddiisssiiiiiss',
            $idUsuario,
            $idCategoria,
            $idSubcategoria,
            $descricao,
            $valor,
            $valorRecebido,
            $juros,
            $desconto,
            $idConta,
            $idFormaTransacao,
            $dataVencimento,
            $dataRecebimento,
            $status,
            $recorrente,
            $parcelado,
            $numeroParcelas,
            $totalParcelas,
            $ultimaParcela,
            $observacoes,
            $grupoReceita
        );

        $stmt->execute();

        // Se já foi recebido, atualiza o saldo da conta
        if ($status === 'recebido' && $idConta) {
            $this->conn->query("
            UPDATE contas_bancarias
            SET saldo_atual = saldo_atual + {$valorRecebido}
            WHERE id_conta = {$idConta}
              AND id_usuario = {$idUsuario}
        ");
        }

        // Gera parcelas futuras
        if ($parcelado && $totalParcelas > 1) {
            $baseDate = new \DateTime($dataVencimento);
            for ($i = $numeroParcelas + 1; $i <= $totalParcelas; $i++) {
                $venc        = (clone $baseDate)->modify('+' . ($i - $numeroParcelas) . ' month')->format('Y-m-d');
                $vpRec       = 0.00;
                $juRec       = 0.00;
                $deRec       = 0.00;
                $dpRec       = null;
                $stRec       = 'previsto';
                $recRec      = 0;
                $parcRec     = 1;
                $numRec      = $i;
                $totRec      = $totalParcelas;
                $ultRec      = ($i === $totalParcelas) ? 1 : 0;
                $obsRec      = $observacoes;
                $grpRec      = $grupoReceita;

                $stmt->bind_param(
                    'iiisddddiisssiiiiiss',
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

        $stmt->close();

        // Gera recorrências mensais (12 meses) se for recorrente e não parcelado
        if ($recorrente && ! $parcelado) {
            // Data original que o usuário passou (YYYY-MM-DD)
            $origem = new \DateTime($dataVencimento);

            // Prepara uma única vez o SQL de recorrência (mesmos 20 campos + NOW())
            $sqlRec = "
        INSERT INTO receitas (
            id_usuario, id_categoria, id_subcategoria, descricao,
            valor, valor_recebido, juros, desconto,
            id_conta, id_forma_transacao,
            data_vencimento, data_recebimento,
            status, recorrente, parcelado,
            numero_parcelas, total_parcelas, ultima_parcela,
            observacoes, grupo_receita,
            criado_em, atualizado_em
        ) VALUES (
            ?,?,?,?,?,?,?,?,?,?,
            ?,?,?,?,?,?,?,?,?,?,
            NOW(), NOW()
        )
    ";
            $stmtRec = $this->conn->prepare($sqlRec);

            for ($i = 1; $i <= 12; $i++) {
                // Cada iteração, parte da data original
                $venc = (clone $origem)
                    ->modify("+{$i} month")
                    ->format('Y-m-d');


                // Campos fixos para toda recorrência
                $vpRec   = 0.00;
                $juRec   = 0.00;
                $deRec   = 0.00;
                $drRec   = null;
                $stRec   = 'previsto';
                $rcRec   = 1;
                $pcRec   = 0;
                $npRec   = 1;
                $tpRec   = 1;
                $upRec   = 1;
                $obsRec  = $observacoes;
                $grpRec  = $grupoReceita;

                // Bind exato como no INSERT principal
                $stmtRec->bind_param(
                    'iiisddddiisssiiiiiss',
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
                    $drRec,
                    $stRec,
                    $rcRec,
                    $pcRec,
                    $npRec,
                    $tpRec,
                    $upRec,
                    $obsRec,
                    $grpRec
                );

                $stmtRec->execute();
            }
            $stmtRec->close();
        }

        return true;
    }

    public function atualizarReceita(array $dados): bool
    {
        // 1) Normaliza campos básicos
        $idUsuario       = (int)   $dados['id_usuario'];
        $idReceita       = (int)   $dados['id_receita'];
        // retira ponto de milhar e converte vírgula antes de cast
        $valorLimpo      = str_replace('.', '', $dados['valor']);
        $valor           = (float) str_replace(',', '.', $valorLimpo);

        // 2) Valor recebido (se existiu)
        $valorRecebido = 0.00;
        if (!empty($dados['valor_recebido'])) {
            $vrLimpo         = str_replace('.', '', $dados['valor_recebido']);
            $valorRecebido   = (float) str_replace(',', '.', $vrLimpo);
        }

        // 3) Status e data de recebimento
        $status = in_array($dados['status'], ['previsto', 'recebido'])
            ? $dados['status']
            : 'previsto';
        $dataRecebimento = ($status === 'recebido' && !empty($dados['data_recebimento']))
            ? $dados['data_recebimento']
            : null;
        if ($status !== 'recebido') {
            // zera se não for recebido
            $valorRecebido   = 0.00;
            $dataRecebimento = null;
        }

        // 4) Juros e desconto
        $juros    = 0.00;
        $desconto = 0.00;
        if ($valorRecebido > $valor) {
            $juros = $valorRecebido - $valor;
        } elseif ($valorRecebido < $valor) {
            $desconto = $valor - $valorRecebido;
        }

        // 5) Outras flags e campos
        $recorrente     = (!empty($dados['recorrente']) && $dados['recorrente'] === '1') ? 1 : 0;
        $parcelado      = (!empty($dados['parcelado'])  && $dados['parcelado'] === '1') ? 1 : 0;
        $numeroParcelas = (int) ($dados['numero_parcelas'] ?? 1);
        $totalParcelas  = (int) ($dados['total_parcelas']  ?? 1);
        $modoEdicao     = $dados['modo_edicao'] ?? 'somente';

        $categoria      = (int)   $dados['id_categoria'];
        $subcategoria   = (int)   $dados['id_subcategoria'];
        $idConta        = (int)   $dados['id_conta'];
        $forma          = (int)   $dados['id_forma_transacao'];
        $dataVencimento = $this->conn->real_escape_string($dados['data_vencimento']);
        $descricao      = $this->conn->real_escape_string($dados['descricao']);
        $obs            = $this->conn->real_escape_string($dados['observacoes']);

        // 6) Busca estado antigo pra ajustar saldo
        $stmtOld = $this->conn->prepare("
    SELECT status, valor_recebido, id_conta, id_forma_transacao
    FROM receitas
    WHERE id_receita = ? AND id_usuario = ?
");
        $stmtOld->bind_param("ii", $idReceita, $idUsuario);
        $stmtOld->execute();
        $original = $stmtOld->get_result()->fetch_assoc();

        if (!empty($dados['id_forma_transacao']) && (int)$dados['id_forma_transacao'] > 0) {
            $forma = (int) $dados['id_forma_transacao'];
        } else {
            $forma = (int) $original['id_forma_transacao'];
        }

        // 7) Se virou não-recebido, devolve o recebido antigo
        if (
            $original['status'] === 'recebido'
            && $status !== 'recebido'
            && !empty($original['id_conta'])
        ) {
            $this->conn->query("
            UPDATE contas_bancarias
            SET saldo_atual = saldo_atual - {$original['valor_recebido']}
            WHERE id_conta = {$original['id_conta']}
        ");
        }

        // 8) Atualiza SOMENTE esta parcela
        if ($modoEdicao === 'somente') {
            // pega se era última parcela
            $ultimaParcela = 0;
            $stmtUpa = $this->conn->prepare("
            SELECT ultima_parcela
            FROM receitas
            WHERE id_receita = ? AND id_usuario = ?
        ");
            $stmtUpa->bind_param("ii", $idReceita, $idUsuario);
            $stmtUpa->execute();
            if ($r = $stmtUpa->get_result()->fetch_assoc()) {
                $ultimaParcela = (int)$r['ultima_parcela'];
            }

            // faz o UPDATE
            $sql = "
          UPDATE receitas SET
            id_categoria        = ?,
            id_subcategoria     = ?,
            descricao           = ?,
            valor               = ?,
            valor_recebido      = ?,
            juros               = ?,
            desconto            = ?,
            id_conta            = ?,
            id_forma_transacao  = ?,
            data_vencimento     = ?,
            data_recebimento    = ?,
            status              = ?,
            recorrente          = ?,
            parcelado           = ?,
            numero_parcelas     = ?,
            total_parcelas      = ?,
            ultima_parcela      = ?,
            observacoes         = ?,
            atualizado_em       = NOW()
          WHERE id_receita = ? AND id_usuario = ?
        ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                'iisddddiisssiiiiisii',
                $categoria,
                $subcategoria,
                $descricao,
                $valor,
                $valorRecebido,
                $juros,
                $desconto,
                $idConta,
                $forma,
                $dataVencimento,
                $dataRecebimento,
                $status,
                $recorrente,
                $parcelado,
                $numero_parcelas,
                $total_parcelas,
                $ultimaParcela,
                $obs,
                $idReceita,
                $idUsuario
            );
            $ok = $stmt->execute();

            if ($ok) {
                // 9a) previsto→recebido soma tudo
                if ($original['status'] !== 'recebido' && $status === 'recebido') {
                    $this->conn->query("
                    UPDATE contas_bancarias
                    SET saldo_atual = saldo_atual + {$valorRecebido}
                    WHERE id_conta = {$idConta} AND id_usuario = {$idUsuario}
                ");
                }
                // 9b) recebido→recebido ajusta diferença
                elseif (
                    $original['status'] === 'recebido'
                    && $status === 'recebido'
                    && $valorRecebido !== (float)$original['valor_recebido']
                ) {
                    $delta = $valorRecebido - (float)$original['valor_recebido'];
                    $this->conn->query("
                    UPDATE contas_bancarias
                    SET saldo_atual = saldo_atual + {$delta}
                    WHERE id_conta = {$idConta} AND id_usuario = {$idUsuario}
                ");
                }
            }

            return $ok;
        }

        // 10) Atualização em lote (futuras ou todas)
        $stmtG = $this->conn->prepare("
        SELECT grupo_receita
        FROM receitas
        WHERE id_receita = ? AND id_usuario = ?
    ");
        $stmtG->bind_param("ii", $idReceita, $idUsuario);
        $stmtG->execute();
        $grupo = $stmtG->get_result()->fetch_assoc()['grupo_receita'] ?? null;
        if (!$grupo) return false;

        $filtro = $modoEdicao === 'futuras'
            ? "AND numero_parcelas >= {$numeroParcelas}"
            : "";

        $sqlBatch = "
        UPDATE receitas SET
            id_categoria       = {$categoria},
            id_subcategoria    = {$subcategoria},
            descricao          = '{$descricao}',
            valor              = {$valor},
            valor_recebido     = {$valorRecebido},
            juros              = {$juros},
            desconto           = {$desconto},
            id_conta           = {$idConta},
            id_forma_transacao = {$forma},
            data_vencimento    = '{$dataVencimento}',
            data_recebimento   = '{$dataRecebimento}',
            status             = '{$status}',
            recorrente         = {$recorrente},
            parcelado          = {$parcelado},
            numero_parcelas    = {$numeroParcelas},
            total_parcelas     = {$totalParcelas},
            observacoes        = '{$obs}',
            atualizado_em      = NOW()
        WHERE id_usuario = {$idUsuario}
          AND grupo_receita = '{$grupo}'
          {$filtro}
    ";

        return (bool)$this->conn->query($sqlBatch);
    }

    public function excluirReceita(int $id, int $idUsuario, string $escopo = 'somente'): bool
    {
        // 1) busca a receita original
        $sql  = "
        SELECT valor_recebido, id_conta, status, grupo_receita, numero_parcelas
        FROM receitas
        WHERE id_receita = ? 
          AND id_usuario = ?
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $idUsuario);
        $stmt->execute();
        $receita = $stmt->get_result()->fetch_assoc();
        if (!$receita) {
            return false;
        }

        $grupo         = $receita['grupo_receita'];
        $numeroParcela = (int)$receita['numero_parcelas'];
        $idsParaExcluir = [];

        // 2) se for “somente” (ou sem grupo), devolve o saldo e marca só esse ID
        if ($escopo === 'somente' || !$grupo) {
            $idsParaExcluir[] = $id;

            if (!empty($receita['id_conta']) && $receita['status'] === 'recebido') {
                $valorEstornado = (float)$receita['valor_recebido'];
                $idConta        = (int)$receita['id_conta'];
                $this->conn->query("
                UPDATE contas_bancarias
                SET saldo_atual = saldo_atual - {$valorEstornado}
                WHERE id_conta = {$idConta}
                  AND id_usuario = {$idUsuario}
            ");
            }
        } else {
            // busca todas as do mesmo grupo
            $filtro = $escopo === 'futuras'
                ? "AND numero_parcelas >= $numeroParcela"
                : "";
            $sql    = "SELECT id_receita, valor_recebido, id_conta, status
                   FROM receitas
                   WHERE grupo_receita = ? AND id_usuario = ? $filtro";
            $stmt   = $this->conn->prepare($sql);
            $stmt->bind_param("si", $grupo, $idUsuario);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $idsParaExcluir[] = (int)$row['id_receita'];
                // devolve saldo se já estava recebido
                if (!empty($row['id_conta']) && $row['status'] === 'recebido') {
                    $valor   = (float)$row['valor_recebido'];
                    $idConta = (int)$row['id_conta'];
                    $this->conn->query("
                    UPDATE contas_bancarias
                    SET saldo_atual = saldo_atual - {$valor}
                    WHERE id_conta = {$idConta}
                ");
                }
            }
        }

        if (empty($idsParaExcluir)) return false;

        $in = implode(',', array_map('intval', $idsParaExcluir));
        return (bool)$this->conn->query("DELETE FROM receitas WHERE id_receita IN ($in)");
    }
}
