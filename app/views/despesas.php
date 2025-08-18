<!-- Estilo exclusivo da página (opcional, se não estiver usando via $viewStyle) -->
<link rel="stylesheet" href="/assets/css/views/despesas.css">

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>



<div class="container">
    <?php $mesAtual = (int)date('m'); ?>

    <div class="filtros-container">

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
            if (!empty($d['id_cartao'])) {
                $formasUsadas[] = 'cartão de crédito';
            } else {
                $formasUsadas[] = $formaStr;
            }
            $contasUsadas[] = $contaStr;
            $categoriasUsadas[] = strtolower(trim($d['nome_categoria'] ?? ''));
            // Se for cartão de crédito, força o nome
            if (!empty($d['id_cartao'])) {
                $formasUsadas[] = 'cartão de crédito';
            } else {
                $formasUsadas[] = strtolower(trim($d['nome_forma'] ?? ''));
            }

            $contasUsadas[] = strtolower(trim($d['nome_conta'] ?? ''));
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

        <div class="filtro">
            <label for="filtro-forma">Tipo de Transação:</label>
            <select id="filtro-forma">
                <option value="">Todas</option>
                <?php foreach ($formas as $forma): ?>
                    <?php if (in_array(strtolower($forma['nome']), $formasUsadas)): ?>
                        <option value="<?= strtolower($forma['nome']) ?>"><?= $forma['nome'] ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if (in_array('cartão de crédito', $formasUsadas)): ?>
                    <option value="cartão de crédito">Cartão de Crédito</option>
                    <option value="exceto_cartao">Exceto Cartão de Crédito</option>
                <?php else: ?>
                <?php endif; ?>
            </select>
        </div>




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

        <div class="filtro busca">
            <label for="filtro-busca">🔍 Buscar:</label>
            <input
                type="text"
                id="filtro-busca"
                placeholder="Descritivo, valor, categoria…"
                style="padding:0.5rem; font-size:1.4rem; border:1px solid var(--corCinzaClaro); border-radius:6px;">
        </div>

        <div class="filtro ordenacao" style="display:flex; gap:.5rem; align-items:center;">
            <label for="filtro-ordem">Ordem:</label>
            <select id="filtro-ordem">
                <option value="venc_asc">Vencimento ↑</option>
                <option value="venc_desc">Vencimento ↓</option>
                <option value="criado_asc">Criado em ↑</option>
                <option value="criado_desc">Criado em ↓</option>
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
                                    data-is-cartao="<?= !empty($d['id_cartao']) ? '1' : '0' ?>"
                                    data-cartao="<?= $d['id_cartao'] ?>"
                                    data-parcelado="<?= $d['parcelado'] ?>"
                                    data-numero-parcelas="<?= $d['numero_parcelas'] ?>"
                                    data-total-parcelas="<?= $d['total_parcelas'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>


                                <!-- Botão Excluir -->
                                <button class="btn-excluir-despesa"
                                    data-id="<?= $d['id_despesa'] ?>"
                                    data-is-cartao="<?= strtolower($d['nome_forma']  ?? '') === 'cartão de crédito' ? '1' : '0' ?>"
                                    data-parcelado="<?= $d['parcelado'] ?>"
                                    data-numero-parcelas="<?= $d['numero_parcelas'] ?>"
                                    data-total-parcelas="<?= $d['total_parcelas'] ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>

                                <!-- Botão Pagar (só se não for cartão e não estiver pago) -->
                                <?php if (strtolower($d['nome_forma']  ?? '') !== 'cartão de crédito' && $d['status'] !== 'pago'): ?>
                                    <button
                                        class="btn-pagar-despesa"
                                        data-id="<?= $d['id_despesa'] ?>"
                                        data-nome="<?= htmlspecialchars($d['descricao']) ?>"
                                        data-valor="<?= number_format($d['valor'], 2, ',', '.') ?>"
                                        data-conta="<?= $d['nome_conta'] ?>"
                                        data-saldo="<?= $d['saldo_atual'] ?>">
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

<!-- scripts -->
<script src="/assets/js/views/despesas-filtros.js"></script>