<link rel="stylesheet" href="/assets/css/views/despesa-cartao-credito.css">

<div class="container">
    <div class="container-fatura-wrapper">
        <div class="cabecalho-fatura">
            <a href="/cartoes" class="btn-voltar"><i class="fas fa-arrow-left"></i></a>
            <h2>Cartão: <?= $cartao['nome_cartao'] ?></h2>

            <div class="fatura-actions">
                <?php
                $valorFatura = array_sum(array_column($despesas, 'valor'));
                ?>

                <button id="btn-adicionar-despesa-cartao"
                    class="icon-btn btn-padrao btn-abrir-despesa"
                    title="Adicionar Despesa Cartão"
                    data-tipo="cartao"
                    data-id-cartao="<?= $cartao['id_cartao'] ?>">
                    <i class="fas fa-plus"></i>
                </button>


                <button
                    class="icon-btn btn-pagar-fatura"
                    data-id-cartao="<?= $cartao['id_cartao'] ?>"
                    data-valor-fatura="<?= $valorFatura ?>"
                    title="Pagar Fatura">
                    <i class="fas fa-check-double"></i>
                </button>


                <div class="search-wrapper" id="search-wrapper">
                    <input type="text" id="busca-fatura" placeholder="Pesquise por descrição, categoria ou valor">
                    <button id="btn-buscar"><i class="fas fa-search"></i></button>
                </div>


                <div class="dropdown-wrapper">
                    <button class="icon-btn btn-dropdown" id="menu-toggle"><i class="fas fa-ellipsis-v"></i></button>
                    <ul class="dropdown-menu" id="menu-opcoes">
                        <li><a href="#">Pagar parcial</a></li>
                        <li><a href="#">Pagar adiantado</a></li>
                        <li>
                            <a
                                href="#"
                                id="menu-estorno"
                                data-id-cartao="<?= $cartao['id_cartao'] ?>"
                                title="Lançar estorno">
                                Lançar estorno
                            </a>
                        </li>
                        <li><a href="#">Ajustar fatura</a></li>
                        <li><a href="#">Limpar fatura</a></li>
                        <li><a href="#">Histórico de faturas</a></li>
                        <li><a href="#">Despesas fixas</a></li>
                        <li><a href="#">Transações ignoradas</a></li>
                        <li><a href="#">Gráfico despesas</a></li>
                        <li><a href="#">Visão geral</a></li>
                        <li><a href="#">Exportar para Excel</a></li>
                        <li><a href="#">Exportar para CSV</a></li>
                    </ul>
                </div>
            </div>
        </div>


        <div class="fatura-conteudo">
            <!-- ===== FILTROS E CONTROLES ===== -->
            <div class="filtros-section">
                <!-- Filtros Avançados -->
                <div class="filtros-avancados">
                    <div class="filtros-header">
                        <h3><i class="fas fa-filter"></i> Filtros Avançados</h3>
                        <button class="btn-toggle-filtros" id="toggle-filtros">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>

                    <div class="filtros-content" id="filtros-content">
                        <div class="filtros-grid">
                            <!-- Filtro por Categoria -->
                            <div class="filtro-group">
                                <label for="filtro-categoria">Categoria</label>
                                <select id="filtro-categoria" class="filtro-select">
                                    <option value="">Todas</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['nome_categoria'] ?>">
                                            <?= $cat['nome_categoria'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Filtro por Status -->
                            <div class="filtro-group">
                                <label for="filtro-status">Status</label>
                                <select id="filtro-status" class="filtro-select">
                                    <option value="">Todos</option>
                                    <option value="pendente">Pendente</option>
                                    <option value="pago">Pago</option>
                                    <option value="atrasado">Atrasado</option>
                                    <option value="estornado">Estornado</option>
                                </select>
                            </div>

                            <!-- Filtro por Valor -->
                            <div class="filtro-group">
                                <label for="filtro-valor-min">Valor Mín.</label>
                                <input type="number" id="filtro-valor-min" class="filtro-input" placeholder="0,00" step="0.01">
                            </div>

                            <div class="filtro-group">
                                <label for="filtro-valor-max">Valor Máx.</label>
                                <input type="number" id="filtro-valor-max" class="filtro-input" placeholder="1000,00" step="0.01">
                            </div>
                        </div>

                        <div class="filtros-actions">
                            <button class="btn-filtrar" id="aplicar-filtros">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <button class="btn-limpar" id="limpar-filtros">
                                <i class="fas fa-times"></i> Limpar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Controles de Mês e Ações -->
                <div class="controles-acao">
                    <div class="filtro-mes">
                        <button><i class="fas fa-chevron-left"></i></button>
                        <span>
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
                            echo $meses[$mes] . ' ' . $ano;
                            ?>
                        </span>
                        <button><i class="fas fa-chevron-right"></i></button>
                    </div>

                    <!-- Ações em Lote -->
                    <div class="acoes-lote" id="acoes-lote" style="display: none;">
                        <span class="selecionados-count">0 selecionados</span>
                        <button class="btn-acao-lote" id="pagar-selecionados">
                            <i class="fas fa-check-double"></i> Pagar
                        </button>
                        <button class="btn-acao-lote" id="excluir-selecionados">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                        <button class="btn-acao-lote" id="desmarcar-todos">
                            <i class="fas fa-times"></i> Limpar
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===== CARDS DE FATURA ===== -->
            <div class="fatura-info">
                <div class="card-info">
                    <i class="fas fa-money-bill-wave"></i>
                    <div>
                        <p>Valor da fatura</p>
                        <strong>R$ <?= number_format(array_sum(array_column($despesas, 'valor')), 2, ',', '.') ?></strong>
                    </div>
                </div>
                <div class="card-info">
                    <i class="fas fa-clipboard-check"></i>
                    <div>
                        <p>Status</p>
                        <strong>Fatura <?= isset($cartao['fatura_aberta']) && $cartao['fatura_aberta'] ? 'aberta' : 'fechada' ?></strong>
                    </div>
                </div>
                <div class="card-info">
                    <i class="fas fa-calendar-minus"></i>
                    <div>
                        <p>Dia de fechamento</p>
                        <strong><?= $cartao['dia_fechamento'] ?> de cada mês</strong>
                    </div>
                </div>
                <div class="card-info">
                    <i class="fas fa-calendar-check"></i>
                    <div>
                        <p>Vencimento</p>
                        <strong><?= $cartao['vencimento_fatura'] ?> de cada mês</strong>
                    </div>
                </div>
            </div>

            <!-- ===== RESUMO PRINCIPAL ===== -->
            <div class="resumo-principal">
                <div class="resumo-cards">
                    <!-- Total da Fatura -->
                    <div class="resumo-card total-fatura">
                        <div class="resumo-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="resumo-info">
                            <h3>Total da Fatura</h3>
                            <span class="resumo-valor">R$ <?= number_format($metricasFatura['total_gasto'] ?? 0, 2, ',', '.') ?></span>
                            <?php if (isset($comparacaoMesAnterior['variacao'])): ?>
                                <div class="resumo-variacao <?= $comparacaoMesAnterior['variacao'] >= 0 ? 'positiva' : 'negativa' ?>">
                                    <i class="fas fa-arrow-<?= $comparacaoMesAnterior['variacao'] >= 0 ? 'up' : 'down' ?>"></i>
                                    <?= abs(number_format($comparacaoMesAnterior['variacao'], 1)) ?>% vs mês anterior
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Total de Transações -->
                    <div class="resumo-card total-transacoes">
                        <div class="resumo-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="resumo-info">
                            <h3>Transações</h3>
                            <span class="resumo-valor"><?= $metricasFatura['total_transacoes'] ?? 0 ?></span>
                            <div class="resumo-detalhes">
                                Média: R$ <?= number_format($metricasFatura['media_transacao'] ?? 0, 2, ',', '.') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Categoria Top -->
                    <div class="resumo-card categoria-top">
                        <div class="resumo-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="resumo-info">
                            <h3>Categoria Top</h3>
                            <span class="resumo-valor"><?= $categoriaMaisUsada['nome_categoria'] ?></span>
                            <div class="resumo-detalhes">
                                R$ <?= number_format($categoriaMaisUsada['total_gasto'], 2, ',', '.') ?> (<?= $categoriaMaisUsada['quantidade'] ?>x)
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="resumo-card status-transacoes">
                        <div class="resumo-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="resumo-info">
                            <h3>Status</h3>
                            <span class="resumo-valor"><?= $metricasFatura['transacoes_pagas'] ?? 0 ?> pagas</span>
                            <div class="resumo-detalhes">
                                <?= $metricasFatura['transacoes_pendentes'] ?? 0 ?> pendentes, <?= $metricasFatura['transacoes_atrasadas'] ?? 0 ?> atrasadas
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== GRÁFICOS ===== -->
            <div class="graficos-section">
                <div class="section-header">
                    <h2><i class="fas fa-chart-bar"></i> Análise Visual</h2>
                    <p>Visualizações interativas dos seus gastos</p>
                </div>

                <div class="graficos-grid">
                    <!-- Gráfico de Distribuição por Categoria -->
                    <div class="grafico-widget">
                        <div class="grafico-header">
                            <h3><i class="fas fa-chart-pie"></i> Distribuição por Categoria</h3>
                            <button class="toggle-grafico" data-target="categorias-pizza">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="grafico-container" id="categorias-pizza">
                            <canvas id="grafico-categorias"
                                data-categorias='<?= json_encode($distribuicaoCategorias, JSON_NUMERIC_CHECK) ?>'></canvas>
                        </div>
                    </div>

                    <!-- Gráfico de Status das Transações -->
                    <div class="grafico-widget">
                        <div class="grafico-header">
                            <h3><i class="fas fa-chart-pie"></i> Status das Transações</h3>
                            <button class="toggle-grafico" data-target="status-transacoes">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="grafico-container" id="status-transacoes">
                            <canvas id="grafico-status"
                                data-status='<?= json_encode([
                                                    'pagas' => $metricasFatura['transacoes_pagas'] ?? 0,
                                                    'pendentes' => $metricasFatura['transacoes_pendentes'] ?? 0,
                                                    'atrasadas' => $metricasFatura['transacoes_atrasadas'] ?? 0
                                                ], JSON_NUMERIC_CHECK) ?>'></canvas>
                        </div>
                    </div>

                    <!-- Top 5 Categorias -->
                    <div class="grafico-widget">
                        <div class="grafico-header">
                            <h3><i class="fas fa-chart-bar"></i> Top 5 Categorias</h3>
                            <button class="toggle-grafico" data-target="top5-categorias">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="grafico-container" id="top5-categorias">
                            <canvas id="grafico-top5"
                                data-top5='<?= json_encode($top5Categorias, JSON_NUMERIC_CHECK) ?>'></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fatura-tabela">

                <table class="tabela-despesas">
                    <thead>
                        <tr>
                            <th class="col-select"> </th>
                            <th>Descrição</th>
                            <th>Categoria</th>
                            <th>Valor</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($despesas as $d): ?>
                            <tr>
                                <td class="col-select">
                                    <input
                                        type="checkbox"
                                        class="row-select"
                                        value="<?= $d['id_fatura'] ?>"
                                        aria-label="Selecionar despesa <?= htmlspecialchars($d['descricao'], ENT_QUOTES) ?>">
                                </td>
                                <td>
                                    <?= $d['descricao'] ?>
                                    <?php if (!empty($d['parcelado'])): ?>
                                        (<?= $d['numero_parcelas'] ?>/<?= $d['total_parcelas'] ?>)
                                    <?php endif; ?>
                                </td>

                                <td><?= $d['nome_categoria'] ?? 'Sem categoria' ?> / <?= $d['nome_subcategoria'] ?? 'Sem subcategoria' ?></td>
                                <td class="valor">R$ <?= number_format($d['valor'], 2, ',', '.') ?></td>
                                <td><?= date('d/m/Y', strtotime($d['data_vencimento'])) ?></td>
                                <td>
                                    <?php
                                    $hoje      = date('Y-m-d');
                                    $dataVenc  = $d['data_vencimento'];
                                    $status    = $d['status'];

                                    if ($status === 'estornado') {
                                        // exibe Estornado
                                        echo '<span class="status estornado">Estornado</span>';
                                    } elseif ($status !== 'pago' && $dataVenc < $hoje) {
                                        // apenas pendentes vencidos chegam aqui
                                        echo '<span class="status atrasado">Atrasado</span>';
                                    } else {
                                        // pago ou pendente dentro do prazo
                                        echo '<span class="status ' . $status . '">' . ucfirst($status) . '</span>';
                                    }
                                    ?>

                                </td>

                                <td>
                                    <!-- Editar -->
                                    <button
                                        class="btn-editar-despesa"
                                        data-id="<?= $d['id_fatura'] ?>"
                                        data-descricao="<?= htmlspecialchars($d['descricao'], ENT_QUOTES) ?>"
                                        data-categoria-id="<?= $d['id_categoria'] ?>"
                                        data-categoria="<?= $d['nome_categoria'] ?>"
                                        data-subcategoria="<?= $d['id_subcategoria'] ?>"
                                        data-conta="<?= htmlspecialchars($d['nome_conta'] ?? '', ENT_QUOTES) ?>"
                                        data-cartao="<?= $cartao['id_cartao'] ?>"
                                        data-forma="<?= $d['id_forma_transacao'] ?>"
                                        data-valor="<?= number_format($d['valor'], 2, ',', '.') ?>"
                                        data-vencimento="<?= $d['data_vencimento'] ?>"
                                        data-status="<?= $d['status'] ?>"
                                        data-valor-pago="<?= number_format($d['valor_pago'] ?? 0, 2, ',', '.') ?>"
                                        data-is-cartao="1"
                                        data-parcelado="<?= $d['parcelado'] ?>"
                                        data-numero-parcelas="<?= $d['numero_parcelas'] ?>"
                                        data-total-parcelas="<?= $d['total_parcelas'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!-- Excluir -->
                                    <button type="button"
                                        class="btn-excluir-despesa"
                                        data-id="<?= $d['id_fatura'] ?>"
                                        data-descricao="<?= htmlspecialchars($d['descricao'], ENT_QUOTES) ?>"
                                        data-valor="R$ <?= number_format($d['valor'], 2, ',', '.') ?>"
                                        data-is-cartao="1"
                                        data-parcelado="<?= $d['parcelado'] ?>"
                                        data-numero-parcelas="<?= $d['numero_parcelas'] ?>"
                                        data-total-parcelas="<?= $d['total_parcelas'] ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>

                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- Modal Pagar Fatura -->
    <div class="modal-custom" id="modalPagarFatura">
        <div class="modal-dialog modal-pagar-fatura">
            <form id="formPagarFatura">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="header-content">
                            <div class="icon-wrapper">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="header-text">
                                <h5 class="modal-title">Pagamento da Fatura</h5>
                                <p class="modal-subtitle">Confirme o pagamento total</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" id="fecharModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="id_cartao" id="id_cartao">

                        <!-- Informações da Fatura -->
                        <div class="fatura-summary">
                            <div class="summary-item">
                                <div class="summary-icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div class="summary-content">
                                    <span class="summary-label">Cartão</span>
                                    <span class="summary-value" id="nome_cartao_modal"><?= $cartao['nome_cartao'] ?></span>
                                </div>
                            </div>

                            <div class="summary-item">
                                <div class="summary-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="summary-content">
                                    <span class="summary-label">Período</span>
                                    <?php
                                    $mesSelecionado = $_GET['mes'] ?? date('n');
                                    $anoSelecionado = $_GET['ano'] ?? date('Y');

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

                                    $nomeMes = $meses[(int)$mesSelecionado] ?? '';
                                    ?>
                                    <span class="summary-value" id="mes_fatura_modal"><?= $nomeMes ?> <?= $anoSelecionado ?></span>
                                </div>
                            </div>

                            <div class="summary-item valor-total">
                                <div class="summary-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="summary-content">
                                    <span class="summary-label">Valor Total</span>
                                    <span class="summary-value valor" id="valor_fatura">R$ 0,00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Campos do Formulário -->
                        <div class="form-section">
                            <div class="form-group">
                                <label for="data_pagamento" class="form-label">
                                    <i class="fas fa-calendar"></i>
                                    Data do Pagamento
                                </label>
                                <input type="date" name="data_pagamento" id="data_pagamento" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="id_conta" class="form-label">
                                    <i class="fas fa-university"></i>
                                    Conta Bancária
                                </label>
                                <select name="id_conta" id="id_conta" class="form-select" required>
                                    <option value="">-- Selecione uma conta --</option>
                                    <?php foreach ($contas as $conta): ?>
                                        <option value="<?= $conta['id_conta'] ?>" data-saldo="<?= $conta['saldo_atual'] ?>">
                                            <?= ucwords(strtolower($conta['nome_conta'])) ?>
                                            (Saldo: R$ <?= number_format($conta['saldo_atual'], 2, ',', '.') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Aviso de Saldo -->
                        <div class="saldo-warning" id="saldoWarning" style="display: none;">
                            <div class="warning-content">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div class="warning-text">
                                    <strong>Atenção:</strong> O pagamento resultará em saldo negativo na conta selecionada.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-cancel" id="cancelarPagamento">
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

    <!-- Modal Pagamento Parcial -->
    <div class="modal-custom" id="modalPagamentoParcial">
        <div class="modal-dialog modal-pagar-fatura">
            <form id="formPagamentoParcial">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="header-content">
                            <div class="icon-wrapper">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="header-text">
                                <h5 class="modal-title">Pagamento Parcial</h5>
                                <p class="modal-subtitle">Selecione as despesas para pagamento</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" id="fecharModalParcial">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="id_cartao" id="id_cartao_parcial">

                        <!-- Lista de Despesas Selecionadas -->
                        <div class="despesas-selecionadas" id="despesas-selecionadas">
                            <!-- Será preenchido via JavaScript -->
                        </div>

                        <!-- Campos do Formulário -->
                        <div class="form-section">
                            <div class="form-group">
                                <label for="data_pagamento_parcial" class="form-label">
                                    <i class="fas fa-calendar"></i>
                                    Data do Pagamento
                                </label>
                                <input type="date" name="data_pagamento" id="data_pagamento_parcial" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="id_conta_parcial" class="form-label">
                                    <i class="fas fa-university"></i>
                                    Conta Bancária
                                </label>
                                <select name="id_conta" id="id_conta_parcial" class="form-select" required>
                                    <option value="">-- Selecione uma conta --</option>
                                    <?php foreach ($contas as $conta): ?>
                                        <option value="<?= $conta['id_conta'] ?>" data-saldo="<?= $conta['saldo_atual'] ?>">
                                            <?= ucwords(strtolower($conta['nome_conta'])) ?>
                                            (Saldo: R$ <?= number_format($conta['saldo_atual'], 2, ',', '.') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Resumo do Pagamento -->
                        <div class="resumo-pagamento">
                            <div class="resumo-item">
                                <span>Total Selecionado:</span>
                                <span id="total-selecionado">R$ 0,00</span>
                            </div>
                            <div class="resumo-item">
                                <span>Quantidade de Itens:</span>
                                <span id="quantidade-itens">0</span>
                            </div>
                        </div>

                        <!-- Aviso de Saldo -->
                        <div class="saldo-warning" id="saldoWarningParcial" style="display: none;">
                            <div class="warning-content">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div class="warning-text">
                                    <strong>Atenção:</strong> O pagamento resultará em saldo negativo na conta selecionada.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-cancel" id="cancelarPagamentoParcial">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-confirm">
                            <i class="fas fa-check"></i>
                            <span class="btn-text">Confirmar Pagamento Parcial</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Estorno Fatura (em baixo de modalPagarFatura) -->
    <div class="modal-custom" id="modalEstorno">
        <div class="modal-dialog">
            <form id="formEstorno">
                <input type="hidden" name="id_cartao" id="estorno_id_cartao">
                <input type="hidden" name="id_categoria" value="24">
                <input type="hidden" name="id_subcategoria" value="182">
                <input type="hidden" name="id_forma_transacao" value="22">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Estorno da Fatura</h5>
                        <button type="button" class="btn-close" data-close="#modalEstorno">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="subtitulo">Confirme o estorno da fatura</p>
                        <div class="info-linha">
                            <strong>Cartão:</strong> <span id="estorno_nome_cartao"><?= $cartao['nome_cartao'] ?></span>
                        </div>
                        <div class="info-linha">
                            <strong>Mês:</strong> <span id="estorno_mes_fatura_modal"><?= $nomeMes ?> <?= $anoSelecionado ?></span>
                        </div>
                        <div class="info-linha">
                            <strong>Valor total:</strong> <span id="estorno_valor_fatura">R$ <?= number_format($valorFatura, 2, ',', '.') ?></span>
                        </div>
                        <div class="mt-3">
                            <label for="estorno_id_conta">Conta para estorno:</label>
                            <select name="id_conta" id="estorno_id_conta" required>
                                <option value="">-- Escolha uma conta --</option>
                                <?php foreach ($contas as $c): ?>
                                    <option value="<?= $c['id_conta'] ?>">
                                        <?= $c['nome_conta'] ?> (Saldo: R$ <?= number_format($c['saldo_atual'], 2, ',', '.') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-close="#modalEstorno">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Confirmar Estorno</button>
                    </div>
                </div>
            </form>
        </div>
    </div>






</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/assets/js/views/despesa-cartao-credito.js"></script>