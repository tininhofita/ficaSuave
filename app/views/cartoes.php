<link rel="stylesheet" href="/assets/css/views/cartoes.css">

<div class="container">
    <button id="btn-adicionar-cartao" class="btn">+ Novo Cartão</button>

    <div class="container">
        <div class="filtro-mes">
            <form method="get" id="form-filtro-mes">
                <label for="mes-filtro">Mês:</label>
                <select id="mes-filtro" name="mes-filtro" onchange="this.form.submit()">
                    <?php foreach ($meses as $mes): ?>
                        <option value="<?= $mes['valor'] ?>" <?= $mes['selecionado'] ? 'selected' : '' ?>>
                            <?= $mes['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
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
                $icone = match ($cartao['bandeira']) {
                    'Visa'             => 'fa-brands fa-cc-visa',
                    'MasterCard'       => 'fa-brands fa-cc-mastercard',
                    'HiperCard'        => 'fa-solid fa-credit-card',
                    'American Express' => 'fa-brands fa-cc-amex',
                    'SoroCard'         => 'fa-solid fa-id-card',
                    'BNDES'            => 'fa-solid fa-building-columns',
                    default            => 'fa-solid fa-credit-card',
                };

                // percentual para a barra
                $percentual = $cartao['limite'] > 0
                    ? ($cartao['limite_disponivel'] / $cartao['limite']) * 100
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
                            data-vencimento_fatura="<?= $cartao['vencimento_fatura'] ?>">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn-excluir" data-id="<?= $cartao['id_cartao'] ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <a href="/cartoes/fatura/<?= $cartao['id_cartao'] ?>" class="card-cartao">
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
                                R$ <?= number_format($cartao['gastos_pendentes'], 2, ',', '.') ?>
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
        <h3 id="titulo-modal-cartao">Novo Cartão</h3>
        <form id="form-cartao">
            <input type="hidden" id="id_cartao" name="id_cartao">

            <div class="campo">
                <label for="limite">Limite</label>
                <input type="text" id="limite" name="limite" required>
            </div>

            <label for="nome_cartao">Nome do Cartão:</label>
            <input type="text" id="nome_cartao" name="nome_cartao" required>

            <div class="bandeira-wrapper">
                <label for="bandeira">Bandeira:</label>
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


            <label for="id_conta">Conta Bancária:</label>
            <select id="id_conta" name="id_conta" required>
                <?php foreach ($contas as $conta): ?>
                    <option value="<?= $conta['id_conta'] ?>"><?= $conta['nome_conta'] ?></option>
                <?php endforeach; ?>
            </select>

            <input type="hidden" id="tipo" name="tipo" value="credito">


            <div class="campo">
                <label for="dia_fechamento">Fechamento</label>
                <input type="number" id="dia_fechamento" name="dia_fechamento" required>
            </div>


            <div class="campo">
                <label for="vencimento_fatura">Vencimento</label>
                <input type="number" id="vencimento_fatura" name="vencimento_fatura" required>
            </div>

            <button type="submit" class="btn">Salvar</button>
            <button type="button" class="btn-cancelar">Cancelar</button>
        </form>
    </div>
</div>

<script src="/assets/js/views/cartoes.js"></script>