<!-- Estilo exclusivo da página (opcional, se não estiver usando via $viewStyle) -->
<link rel="stylesheet" href="/assets/css/views/despesas.css">

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>



<div class="container">
    <!-- Header da página -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Despesas</h1>
            <p class="page-subtitle">Gerencie suas despesas e mantenha o controle financeiro</p>
        </div>
    </div>

    <!-- Cards de estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon pendente">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <h3 class="stat-card-value"><?= count(array_filter($despesas, fn($d) => $d['status'] === 'pendente')) ?></h3>
                <p class="stat-card-label">Pendentes</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon pago">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <h3 class="stat-card-value"><?= count(array_filter($despesas, fn($d) => $d['status'] === 'pago')) ?></h3>
                <p class="stat-card-label">Pagas</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon atrasado">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <h3 class="stat-card-value"><?= count(array_filter($despesas, fn($d) => $d['status'] === 'atrasado')) ?></h3>
                <p class="stat-card-label">Atrasadas</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon total">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <h3 class="stat-card-value">R$ <?= number_format(array_sum(array_column($despesas, 'valor')), 2, ',', '.') ?></h3>
                <p class="stat-card-label">Total</p>
            </div>
        </div>
    </div>

    <!-- Conteúdo principal -->
    <div class="main-content">

        <!-- Filtros -->
        <div class="filtros-container">
            <!-- Botão Nova Despesa -->
            <div class="filtro">
                <button class="btn-nova-despesa btn btn-primary" data-tipo="normal">
                    <i class="fas fa-plus"></i>
                    Nova Despesa
                </button>
            </div>

            <?php
            $formasUsadas = [];
            $contasUsadas = [];
            $categoriasUsadas = [];
            $mesesUsados = [];
            $anosUsados = [];

            foreach ($despesas as $d) {
                // normaliza pra não dar null
                $formaStr = strtolower(trim($d['nome_forma'] ?? ''));
                $contaStr = strtolower(trim($d['nome_conta']  ?? ''));
                $formasUsadas[] = $formaStr;
                $contasUsadas[] = $contaStr;
                $categoriasUsadas[] = strtolower(trim($d['nome_categoria'] ?? ''));

                if (!empty($d['data_vencimento'])) {
                    $mes = date('n', strtotime($d['data_vencimento']));
                    $mesesUsados[] = $mes;
                }

                if (!empty($d['data_vencimento'])) {
                    $ano = date('Y', strtotime($d['data_vencimento']));
                    $anosUsados[] = $ano;
                }
            }



            // Remove duplicados
            $anosUsados = array_unique($anosUsados ?? []);
            sort($anosUsados);
            $anoAtual = date('Y');
            $formasUsadas = array_unique($formasUsadas ?? []);
            $contasUsadas = array_unique($contasUsadas ?? []);
            $categoriasUsadas = array_unique($categoriasUsadas ?? []);
            $mesesUsados = array_unique($mesesUsados ?? []);

            ?>

            <!-- Filtro Status -->
            <div class="filtro">
                <label for="filtro-status">Status:</label>
                <select id="filtro-status">
                    <option value="" selected>Todos</option>
                    <option value="pago">Pago</option>
                    <option value="pendente">Pendente</option>
                    <option value="atrasado">Atrasado</option>
                    <option value="estornado">Estornado</option>
                </select>
            </div>

            <?php
            $meses = [
                1 => 'Janeiro',
                2 => 'Fevereiro',
                3 => 'Março',
                4 => 'Abril',
                5 => 'Maio',
                6 => 'Junho',
                7 => 'Julho',
                8 => 'Agosto',
                9 => 'Setembro',
                10 => 'Outubro',
                11 => 'Novembro',
                12 => 'Dezembro'
            ];
            $mesAtual = date('n');
            ?>

            <!-- Filtro Ano -->
            <div class="filtro">
                <label for="filtro-ano">Ano:</label>
                <select id="filtro-ano">
                    <option value="">Todos</option>
                    <?php foreach ($anosUsados as $ano): ?>
                        <option value="<?= $ano ?>"
                            <?= $ano == $anoAtual ? 'selected' : '' ?>>
                            <?= $ano ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro Mês -->
            <div class="filtro">
                <label for="filtro-mes">Mês:</label>
                <select id="filtro-mes">
                    <option value="">Todos</option>
                    <?php foreach ($meses as $i => $mesNome): ?>
                        <?php if (in_array($i, $mesesUsados)): ?>
                            <option value="<?= $i ?>" <?= $i === (int)$mesAtual ? 'selected' : '' ?>>
                                <?= $mesNome ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </select>
            </div>

            <!-- Filtro Conta -->
            <div class="filtro">
                <label for="filtro-conta">Conta:</label>
                <select id="filtro-conta">
                    <option value="">Todas</option>
                    <?php foreach ($contas as $conta): ?>
                        <?php if (in_array(strtolower($conta['nome_conta']), $contasUsadas)): ?>
                            <option value="<?= strtolower($conta['nome_conta']) ?>"><?= $conta['nome_conta'] ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro Tipo de Transação -->
            <div class="filtro">
                <label for="filtro-forma">Tipo de Transação:</label>
                <select id="filtro-forma">
                    <option value="">Todas</option>
                    <?php foreach ($formas as $forma): ?>
                        <?php if (in_array(strtolower($forma['nome']), $formasUsadas)): ?>
                            <option value="<?= strtolower($forma['nome']) ?>"><?= $forma['nome'] ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </select>
            </div>

            <!-- Filtro Categoria -->
            <div class="filtro">
                <label for="filtro-categoria">Categoria:</label>
                <select id="filtro-categoria">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <?php if (in_array(strtolower($cat['nome_categoria']), $categoriasUsadas)): ?>
                            <option value="<?= strtolower($cat['nome_categoria']) ?>"><?= $cat['nome_categoria'] ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro Busca -->
            <div class="filtro busca">
                <label for="filtro-busca">🔍 Buscar:</label>
                <input
                    type="text"
                    id="filtro-busca"
                    placeholder="Descritivo, valor, categoria…">
            </div>

            <div class="filtro ordenacao" style="display:flex; gap:.5rem; align-items:center;">
                <label for="filtro-ordem">Ordem:</label>
                <select id="filtro-ordem">
                    <option value="venc_asc">Vencimento ↑</option>
                    <option value="venc_desc">Vencimento ↓</option>
                    <option value="criado_asc">Criado em ↑</option>
                    <option value="criado_desc" selected>Criado em ↓</option>
                    <option value="valor_asc">Valor ↑</option>
                    <option value="valor_desc">Valor ↓</option>
                    <option value="desc_asc">Descrição A→Z</option>
                    <option value="desc_desc">Descrição Z→A</option>
                </select>
            </div>



        </div>

        <!-- Tabela -->
        <div class="tabela-wrapper">
            <table class="tabela-despesas">
                <colgroup>
                    <col> <!-- Status -->
                    <col> <!-- Vencimento -->
                    <col> <!-- Descrição -->
                    <col> <!-- Categoria -->
                    <col> <!-- Subcategoria -->
                    <col> <!-- Conta -->
                    <col> <!-- Tipo de Transação -->
                    <col> <!-- Valor -->
                    <col> <!-- Ações -->
                </colgroup>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Vencimento</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Subcategoria</th>
                        <th>Conta</th>
                        <th>Tipo de Transação</th>
                        <th>Valor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($despesas as $d):
                        $formaStr = strtolower(trim($d['nome_forma'] ?? ''));
                    ?>
                        <?php
                        $vencRaw   = !empty($d['data_vencimento']) ? date('Y-m-d', strtotime($d['data_vencimento'])) : '';
                        $criadoRaw = !empty($d['criado_em'])       ? date('Y-m-d H:i:s', strtotime($d['criado_em'])) : '';
                        $valorRaw  = number_format((float)$d['valor'], 2, '.', '');
                        ?>
                        <tr
                            data-vencimento="<?= htmlspecialchars($vencRaw,   ENT_QUOTES) ?>"
                            data-criado="<?= htmlspecialchars($criadoRaw, ENT_QUOTES) ?>"
                            data-valor="<?= htmlspecialchars($valorRaw,  ENT_QUOTES) ?>"
                            data-status="<?= htmlspecialchars(strtolower($d['status']), ENT_QUOTES) ?>">

                            <td><span class="status <?= $d['status'] ?>"><?= ucfirst($d['status']) ?></span></td>
                            <td><?= date('d/m/Y', strtotime($d['data_vencimento'])) ?></td>

                            <td>
                                <?= $d['descricao'] ?>
                                <?php if ($d['parcelado']): ?>
                                    - <?= $d['numero_parcelas'] ?>/<?= $d['total_parcelas'] ?>
                                <?php endif; ?>
                            </td>

                            <td><?= $d['nome_categoria'] ?></td>
                            <td><?= $d['nome_subcategoria'] ?></td>
                            <td><?= htmlspecialchars($d['nome_conta'] ?? '-', ENT_QUOTES) ?></td>
                            <td><?= htmlspecialchars($d['nome_forma'] ?? '-', ENT_QUOTES) ?></td>
                            <td>R$ <?= number_format($d['valor'], 2, ',', '.') ?></td>
                            <td>
                                <div class="acoes">
                                    <button
                                        class="btn-editar-despesa"
                                        data-id="<?= $d['id_despesa'] ?>"
                                        data-descricao="<?= htmlspecialchars($d['descricao'], ENT_QUOTES) ?>"
                                        data-categoria-id="<?= $d['id_categoria'] ?>"
                                        data-subcategoria="<?= $d['id_subcategoria'] ?>"
                                        data-conta-id="<?= $d['id_conta'] ?>"
                                        data-forma-id="<?= $d['id_forma_transacao'] ?>"
                                        data-valor="<?= number_format($d['valor'], 2, ',', '.') ?>"
                                        data-vencimento="<?= $d['data_vencimento'] ?>"
                                        data-status="<?= $d['status'] ?>"
                                        data-valor-pago="<?= number_format($d['valor_pago'] ?? 0, 2, ',', '.') ?>"
                                        data-parcelado="<?= $d['parcelado'] ?>"
                                        data-numero-parcelas="<?= $d['numero_parcelas'] ?>"
                                        data-total-parcelas="<?= $d['total_parcelas'] ?>"
                                        data-is-cartao="0">
                                        <i class="fas fa-edit"></i>
                                    </button>


                                    <!-- Botão Excluir -->
                                    <button class="btn-excluir-despesa"
                                        data-id="<?= $d['id_despesa'] ?>"
                                        data-parcelado="<?= $d['parcelado'] ?>"
                                        data-numero-parcelas="<?= $d['numero_parcelas'] ?>"
                                        data-total-parcelas="<?= $d['total_parcelas'] ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>

                                    <!-- Botão Pagar (só se não estiver pago) -->
                                    <?php if ($d['status'] !== 'pago'): ?>
                                        <button
                                            class="btn-pagar-despesa"
                                            data-id="<?= $d['id_despesa'] ?>"
                                            data-nome="<?= htmlspecialchars($d['descricao']) ?>"
                                            data-valor="<?= number_format($d['valor'], 2, ',', '.') ?>"
                                            data-conta="<?= $d['nome_conta'] ?>"
                                            data-saldo="<?= $d['saldo_atual'] ?? 0 ?>">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Pagar Despesa -->
<div class="modal-pagar-despesa" id="modalPagarDespesa">
    <div class="modal-dialog">
        <form id="formPagarDespesa">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="header-content">
                        <div class="icon-wrapper">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="header-text">
                            <h5 class="modal-title">Confirmar Pagamento</h5>
                            <p class="modal-subtitle">Confirme os dados do pagamento</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" id="fecharModalPagamento">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_despesa" id="id_despesa_pagamento">

                    <div class="despesa-summary">
                        <div class="summary-item">
                            <div class="summary-icon">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">Despesa</div>
                                <div class="summary-value" id="nome_despesa_pagamento">-</div>
                            </div>
                        </div>

                        <div class="summary-item">
                            <div class="summary-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-label">Valor Original</div>
                                <div class="summary-value" id="valor_original_pagamento">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-group">
                            <label for="data_pagamento_despesa" class="form-label">
                                <i class="fas fa-calendar"></i>
                                Data do Pagamento
                            </label>
                            <input type="date" name="data_pagamento" id="data_pagamento_despesa" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="valor_pagamento_despesa" class="form-label">
                                <i class="fas fa-money-bill"></i>
                                Valor a Pagar
                            </label>
                            <input type="text" name="valor" id="valor_pagamento_despesa" class="form-control" placeholder="0,00" required>
                        </div>

                        <div class="form-group">
                            <label for="conta_pagamento_despesa" class="form-label">
                                <i class="fas fa-university"></i>
                                Conta Bancária
                            </label>
                            <select name="id_conta" id="conta_pagamento_despesa" class="form-select" required>
                                <option value="">-- Selecione uma conta --</option>
                                <?php foreach ($contas as $conta): ?>
                                    <option value="<?= $conta['id_conta'] ?>" data-saldo="<?= $conta['saldo_atual'] ?? 0 ?>">
                                        <?= ucwords(strtolower($conta['nome_conta'])) ?>
                                        (Saldo: R$ <?= number_format($conta['saldo_atual'] ?? 0, 2, ',', '.') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="saldo-warning" id="saldoWarningDespesa" style="display: none;">
                        <div class="warning-content">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div class="warning-text">
                                <strong>Atenção:</strong> O pagamento resultará em saldo negativo na conta selecionada.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" id="cancelarPagamentoDespesa">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-confirm">
                        <i class="fas fa-check"></i>
                        <span class="btn-text">Confirmar Pagamento</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Confirmar Exclusão -->
<div class="modal-excluir-despesa" id="modalExcluirDespesa">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="header-content">
                    <div class="icon-wrapper">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <div class="header-text">
                        <h5 class="modal-title">Confirmar Exclusão</h5>
                        <p class="modal-subtitle">Esta ação não pode ser desfeita</p>
                    </div>
                </div>
                <button type="button" class="btn-close" id="fecharModalExclusao">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="despesa-summary">
                    <div class="summary-item">
                        <div class="summary-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="summary-content">
                            <div class="summary-label">Despesa</div>
                            <div class="summary-value" id="nome_despesa_exclusao">-</div>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="summary-content">
                            <div class="summary-label">Valor</div>
                            <div class="summary-value" id="valor_despesa_exclusao">-</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-group">
                        <label for="escopo_exclusao" class="form-label">
                            <i class="fas fa-cogs"></i>
                            Escopo da Exclusão
                        </label>
                        <select id="escopo_exclusao" class="form-select">
                            <option value="somente">Apenas esta despesa</option>
                            <option value="futuras">Esta e as futuras</option>
                            <option value="todas">Todas as parcelas</option>
                        </select>
                    </div>
                </div>

                <div class="warning-section">
                    <div class="warning-content">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div class="warning-text">
                            <strong>Atenção:</strong> Se a despesa estiver paga, o valor será devolvido ao saldo da conta.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" id="cancelarExclusao">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmarExclusao">
                    <i class="fas fa-trash-alt"></i>
                    <span class="btn-text">Confirmar Exclusão</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- scripts -->
<script src="/assets/js/views/despesas-filtros.js"></script>