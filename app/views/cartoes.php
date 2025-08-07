<link rel="stylesheet" href="/assets/css/views/cartoes.css">

<div class="container">
    <button id="btn-adicionar-cartao" class="btn">+ Novo Cartão</button>

    <div class="container">
        <div class="filtro-faturas">
            <form method="get" id="form-filtro-fatura">
                <label for="filtro-fatura">Exibir:</label>
                <select id="filtro-fatura" name="filtro-fatura" onchange="this.form.submit()">
                    <option value="aberta" <?= $filter === 'aberta'  ? 'selected' : '' ?>>Faturas Abertas</option>
                    <option value="fechada" <?= $filter === 'fechada' ? 'selected' : '' ?>>Faturas Fechadas</option>
                </select>
            </form>
        </div>

        <div class="grid-cartoes">
            <?php foreach ($cartoes as $cartao): ?>
                <?php
                // --- calcula as variáveis de exibição ---
                $isClosed = $cartao['is_closed_this_month'];
                if ($filter === 'aberta') {
                    // se ESTE mês ainda não fechou, mostro ESTE, senão próximo
                    if (!$isClosed) {
                        $dt  = $cartao['fecha_este_mes_dt'];
                        $vl  = $cartao['fatura_atual'];
                    } else {
                        $dt  = $cartao['fecha_proximo_dt'];
                        $vl  = $cartao['fatura_proxima'];
                    }
                    $txtStatus = 'Fatura aberta';
                    $lblValor  = 'Valor parcial';
                } else {
                    // fechadas → mostro sempre ESTE mês
                    $dt  = $cartao['fecha_este_mes_dt'];
                    $vl  = $cartao['fatura_atual'];
                    $txtStatus = 'Fatura fechada';
                    $lblValor  = 'Valor total';
                }

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