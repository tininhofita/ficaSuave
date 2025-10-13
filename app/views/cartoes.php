<link rel="stylesheet" href="/assets/css/views/cartoes.css">

<div class="container">
    <!-- ===== HEADER DA PÁGINA ===== -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Cartões de Crédito</h1>
            <p class="page-subtitle">Gerencie seus cartões e acompanhe os gastos</p>
        </div>
    </div>

    <!-- ===== ESTATÍSTICAS ===== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon total">
                    <i class="fas fa-credit-card"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-value"><?= count($cartoes) ?></p>
                <p class="stat-card-label">Total de Cartões</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon pendente">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-value">R$ <?= number_format(array_sum(array_column($cartoes, 'gastos_pendentes')), 2, ',', '.') ?></p>
                <p class="stat-card-label">Gastos Pendentes</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon pago">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-value">R$ <?= number_format(array_sum(array_column($cartoes, 'limite')), 2, ',', '.') ?></p>
                <p class="stat-card-label">Limite Total</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon atrasado">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-value">R$ <?= number_format(array_sum(array_column($cartoes, 'limite_disponivel')), 2, ',', '.') ?></p>
                <p class="stat-card-label">Limite Disponível</p>
            </div>
        </div>
    </div>

    <!-- ===== CONTEÚDO PRINCIPAL ===== -->
    <div class="main-content">
        <div class="filtros-container">
            <div class="filtro">
                <button id="btn-adicionar-cartao" class="btn btn-novo-cartao">
                    <i class="fas fa-plus"></i>
                    <span>Novo Cartão</span>
                </button>
            </div>
        </div>

        <div class="grid-cartoes">
            <?php foreach ($cartoes as $cartao): ?>
                <?php
                // --- calcula as variáveis de exibição ---
                $faturaFechada = $cartao['fatura_fechada'];
                $dt = $cartao['data_fechamento'];
                $vl = $cartao['fatura_valor'];

                // Status da fatura baseado na data de fechamento
                $txtStatus = $faturaFechada ? 'Fatura fechada' : 'Fatura aberta';
                $lblValor = 'Valor atual';

                // ícone da bandeira
                $bandeiras = [
                    'Visa'             => 'fa-brands fa-cc-visa',
                    'MasterCard'       => 'fa-brands fa-cc-mastercard',
                    'HiperCard'        => 'fa-solid fa-credit-card',
                    'American Express' => 'fa-brands fa-cc-amex',
                    'SoroCard'         => 'fa-solid fa-id-card',
                    'BNDES'            => 'fa-solid fa-building-columns',
                ];
                $icone = $bandeiras[$cartao['bandeira']] ?? 'fa-solid fa-credit-card';

                // percentual para a barra (baseado nos gastos não pagos)
                $percentual = $cartao['limite'] > 0
                    ? ($cartao['gastos_nao_pagos'] / $cartao['limite']) * 100
                    : 0;
                ?>
                <div class="card-wrapper">
                    <div class="acoes">
                        <button class="btn-editar"
                            data-id="<?= $cartao['id_cartao'] ?>"
                            data-limite="<?= $cartao['limite'] ?>"
                            data-nome="<?= $cartao['nome_cartao'] ?>"
                            data-bandeira="<?= $cartao['bandeira'] ?>"
                            data-conta="<?= $cartao['id_conta'] ?>"
                            data-dia_fechamento="<?= $cartao['dia_fechamento'] ?>"
                            data-vencimento_fatura="<?= $cartao['vencimento_fatura'] ?>"
                            data-cor="<?= $cartao['cor_cartao'] ?? '#3b82f6' ?>">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn-excluir" data-id="<?= $cartao['id_cartao'] ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <a href="/cartoes/fatura/<?= $cartao['id_cartao'] ?>" class="card-cartao" data-cor="<?= $cartao['cor_cartao'] ?? '#3b82f6' ?>">
                        <div class="cabecalho">
                            <i class="<?= $icone ?>"></i>
                            <strong><?= ucwords(strtolower($cartao['nome_cartao'])) ?></strong>
                        </div>

                        <div class="conteudo-cartao">
                            <p class="info-fatura"><?= $txtStatus ?></p>
                            <p class="valor-fatura">
                                <?= $lblValor ?>:
                                <strong class="vermelho">R$ <?= number_format($vl, 2, ',', '.') ?></strong>
                            </p>

                            <?php if (!empty($cartao['status_despesas_mes'])): ?>
                                <p class="status-mes">
                                    Status: <strong class="status-<?= $cartao['status_despesas_mes'] ?>"><?= ucfirst($cartao['status_despesas_mes']) ?></strong>
                                </p>
                            <?php endif; ?>

                            <p>Fecha em <strong><?= $dt->format('d/m/Y') ?></strong></p>

                            <p>
                                R$ <?= number_format($cartao['gastos_nao_pagos'], 2, ',', '.') ?>
                                de R$ <?= number_format($cartao['limite'], 2, ',', '.') ?>
                            </p>
                            <div class="barra">
                                <div class="progresso" style="width:<?= round($percentual, 1) ?>%;"></div>
                            </div>
                            <small>
                                Limite disponível R$ <?= number_format($cartao['limite_disponivel'], 2, ',', '.') ?>
                                (<?= round($percentual) ?>%)
                            </small>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>



<!-- Modal de Cadastro de Cartão -->
<div id="modal-cartao" class="modal">
    <div class="modal-conteudo">
        <div class="modal-header">
            <div class="header-content">
                <div class="icon-wrapper">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="header-text">
                    <h3 id="titulo-modal-cartao" class="modal-title">Novo Cartão</h3>
                    <p class="modal-subtitle">Configure os dados do cartão de crédito</p>
                </div>
            </div>
            <button type="button" class="btn-close" id="fecharModalCartao">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="form-cartao">
            <input type="hidden" id="id_cartao" name="id_cartao">

            <div class="form-section">
                <div class="form-group">
                    <label for="nome_cartao" class="form-label">
                        <i class="fas fa-credit-card"></i>
                        Nome do Cartão
                    </label>
                    <input type="text" id="nome_cartao" name="nome_cartao" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="limite" class="form-label">
                        <i class="fas fa-dollar-sign"></i>
                        Limite
                    </label>
                    <input type="text" id="limite" name="limite" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="bandeira" class="form-label">
                        <i class="fas fa-flag"></i>
                        Bandeira
                    </label>
                    <div class="custom-select" id="bandeira-select">
                        <input type="hidden" name="bandeira" id="bandeira">
                        <button type="button" class="select-toggle">Selecione a bandeira</button>
                        <ul class="select-options">
                            <li data-bandeira="Visa"><i class="fa-brands fa-cc-visa"></i> Visa</li>
                            <li data-bandeira="MasterCard"><i class="fa-brands fa-cc-mastercard"></i> MasterCard</li>
                            <li data-bandeira="HiperCard"><i class="fa-solid fa-credit-card"></i> HiperCard</li>
                            <li data-bandeira="American Express"><i class="fa-brands fa-cc-amex"></i> American Express</li>
                            <li data-bandeira="SoroCard"><i class="fa-solid fa-id-card"></i> SoroCard</li>
                            <li data-bandeira="BNDES"><i class="fa-solid fa-building-columns"></i> BNDES</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_conta" class="form-label">
                        <i class="fas fa-university"></i>
                        Conta Bancária
                    </label>
                    <select id="id_conta" name="id_conta" class="form-select" required>
                        <?php foreach ($contas as $conta): ?>
                            <option value="<?= $conta['id_conta'] ?>"><?= $conta['nome_conta'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="dia_fechamento" class="form-label">
                        <i class="fas fa-calendar-day"></i>
                        Dia de Fechamento
                    </label>
                    <input type="number" id="dia_fechamento" name="dia_fechamento" class="form-control" min="1" max="31" required>
                </div>

                <div class="form-group">
                    <label for="vencimento_fatura" class="form-label">
                        <i class="fas fa-calendar-check"></i>
                        Dia de Vencimento
                    </label>
                    <input type="number" id="vencimento_fatura" name="vencimento_fatura" class="form-control" min="1" max="31" required>
                </div>

                <div class="form-group">
                    <label for="cor_cartao" class="form-label">
                        <i class="fas fa-palette"></i>
                        Cor do Cartão
                    </label>
                    <div class="color-picker">
                        <input type="hidden" name="cor_cartao" id="cor_cartao" value="blue">
                        <div class="color-options">
                            <div class="color-option" data-color="purple" style="background: linear-gradient(135deg, #8b5cf6, #a855f7, #c084fc);"></div>
                            <div class="color-option" data-color="blue" style="background: linear-gradient(135deg, #3b82f6, #60a5fa, #93c5fd);"></div>
                            <div class="color-option" data-color="green" style="background: linear-gradient(135deg, #10b981, #34d399, #6ee7b7);"></div>
                            <div class="color-option" data-color="red" style="background: linear-gradient(135deg, #ef4444, #f87171, #fca5a5);"></div>
                            <div class="color-option" data-color="orange" style="background: linear-gradient(135deg, #f97316, #fb923c, #fdba74);"></div>
                            <div class="color-option" data-color="pink" style="background: linear-gradient(135deg, #ec4899, #f472b6, #f9a8d4);"></div>
                            <div class="color-option" data-color="indigo" style="background: linear-gradient(135deg, #6366f1, #818cf8, #a5b4fc);"></div>
                            <div class="color-option" data-color="teal" style="background: linear-gradient(135deg, #14b8a6, #5eead4, #99f6e4);"></div>
                            <div class="color-option" data-color="gray" style="background: linear-gradient(135deg, #6b7280, #9ca3af, #d1d5db);"></div>
                            <div class="color-option" data-color="yellow" style="background: linear-gradient(135deg, #eab308, #facc15, #fde047);"></div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="tipo" name="tipo" value="credito">

            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" id="cancelarModalCartao">
                    <i class="fas fa-times"></i>
                    <span class="btn-text">Cancelar</span>
                </button>
                <button type="submit" class="btn btn-confirm">
                    <i class="fas fa-check"></i>
                    <span class="btn-text">Salvar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/views/cartoes.js"></script>