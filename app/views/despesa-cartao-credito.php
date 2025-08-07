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
            <div class="fatura-tabela">

                <div class="filtro-mes">
                    <button><i class="fas fa-chevron-left"></i></button>
                    <span><?= date('F Y') ?></span>
                    <button><i class="fas fa-chevron-right"></i></button>
                </div>

                <table class="tabela-despesas">
                    <thead>
                        <tr>
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
                                <td>
                                    <?= $d['descricao'] ?>
                                    <?php if (!empty($d['parcelado'])): ?>
                                        (<?= $d['numero_parcelas'] ?>/<?= $d['total_parcelas'] ?>)
                                    <?php endif; ?>
                                </td>

                                <td><?= $d['nome_categoria'] ?> / <?= $d['nome_subcategoria'] ?></td>
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
                                        data-id="<?= $d['id_despesa'] ?>"
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
                                    <button
                                        class="btn-excluir-despesa"
                                        data-id="<?= $d['id_despesa'] ?>"
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
        </div>
    </div>

    <!-- Modal Pagar Fatura -->
    <div class="modal-custom" id="modalPagarFatura">
        <div class="modal-dialog">
            <form id="formPagarFatura">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pagamento da Fatura</h5>
                        <button type="button" class="btn-close" id="fecharModal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="id_cartao" id="id_cartao">

                        <p class="subtitulo">Pagamento total da fatura</p>

                        <div class="info-linha">
                            <strong>Cartão:</strong> <span id="nome_cartao_modal"><?= $cartao['nome_cartao'] ?></span>
                        </div>

                        <div class="info-linha">
                            <strong>Fatura:</strong>
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
                            <span id="mes_fatura_modal"><?= $nomeMes ?> <?= $anoSelecionado ?></span>


                        </div>

                        <div class="info-linha">
                            <strong>Valor:</strong>
                            <span id="valor_fatura">R$ 0,00</span>
                        </div>

                        <div class="mt-3">
                            <label for="id_conta">Selecione a conta bancária:</label>
                            <select name="id_conta" id="id_conta" class="form-select" required>
                                <option value="">-- Escolha uma conta --</option>
                                <?php foreach ($contas as $conta): ?>
                                    <option value="<?= $conta['id_conta'] ?>">
                                        <?= ucwords(strtolower($conta['nome_conta'])) ?> (Saldo: R$ <?= number_format($conta['saldo_atual'], 2, ',', '.') ?>)
                                    </option>


                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="cancelarPagamento">Cancelar</button>
                        <button type="submit" class="btn btn-success">Confirmar Pagamento</button>
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

<script src="/assets/js/views/despesa-cartao-credito.js"></script>