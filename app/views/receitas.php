<!-- Estilo exclusivo da página (opcional, se não estiver usando via $viewStyle) -->
<link rel="stylesheet" href="/assets/css/views/receitas.css">

<div class="container">

    <div class="filtros-container">

        <?php
        $formasUsadas = [];
        $contasUsadas = [];
        $categoriasUsadas = [];
        $mesesUsados = [];

        foreach ($receitas as $receita) {
            // normaliza pra não dar null
            $formaStr = strtolower(trim($receita['nome_forma'] ?? ''));
            $contaStr = strtolower(trim($receita['nome_conta']  ?? ''));
            if (!empty($receita['id_cartao'])) {
                $formasUsadas[] = 'cartão de crédito';
            } else {
                $formasUsadas[] = $formaStr;
            }
            $contasUsadas[] = $contaStr;
            $categoriasUsadas[] = strtolower(trim($receita['nome_categoria'] ?? ''));
            // Se for cartão de crédito, força o nome
            if (!empty($receita['id_cartao'])) {
                $formasUsadas[] = 'cartão de crédito';
            } else {
                $formasUsadas[] = strtolower(trim($receita['nome_forma'] ?? ''));
            }

            $contasUsadas[] = strtolower(trim($receita['nome_conta'] ?? ''));
            $categoriasUsadas[] = strtolower(trim($receita['nome_categoria'] ?? ''));

            if (!empty($receita['data_vencimento'])) {
                $mes = date('n', strtotime($receita['data_vencimento']));
                $mesesUsados[] = $mes;
            }
        }



        // Remove duplicados
        $formasUsadas = array_unique($formasUsadas ?? []);
        $contasUsadas = array_unique($contasUsadas ?? []);
        $categoriasUsadas = array_unique($categoriasUsadas ?? []);
        $mesesUsados = array_unique($mesesUsados ?? []);

        ?>


        <div class="filtro">
            <label for="filtro-status">Status:</label>
            <select id="filtro-status">
                <option value="">Todos</option>
                <option value="recebido">Recebido</option>
                <option value="previsto">Previsto</option>
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


    </div>

    <!-- Tabela -->
    <table class="tabela-receitas">
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
                <th>Valor Recebido</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($receitas as $receita):
                $formaStr = strtolower(trim($receita['nome_forma'] ?? ''));
            ?>
                <tr>
                    <td><span class="status <?= $receita['status'] ?>"><?= ucfirst($receita['status']) ?></span></td>
                    <td><?= date('d/m/Y', strtotime($receita['data_vencimento'])) ?></td>
                    <td>
                        <?= $receita['descricao'] ?>
                        <?php if ($receita['parcelado']): ?>
                            - <?= $receita['numero_parcelas'] ?>/<?= $receita['total_parcelas'] ?>
                        <?php endif; ?>
                    </td>
                    <td><?= $receita['nome_categoria'] ?></td>
                    <td><?= $receita['nome_subcategoria'] ?></td>
                    <td><?= htmlspecialchars($receita['nome_conta'] ?? '-', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($receita['nome_forma'] ?? '-', ENT_QUOTES) ?></td>
                    <td>R$ <?= number_format($receita['valor'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($receita['valor_recebido'] ?? 0, 2, ',', '.') ?></td>
                    <td>
                        <button
                            class="btn-editar-receita"
                            data-id="<?= $receita['id_receita'] ?>"
                            data-descricao="<?= htmlspecialchars($receita['descricao'], ENT_QUOTES) ?>"
                            data-categoria="<?= $receita['id_categoria'] ?>"
                            data-subcategoria="<?= $receita['id_subcategoria'] ?>"
                            data-conta="<?= $receita['id_conta'] ?>"
                            data-forma="<?= $receita['id_forma_transacao'] ?>"
                            data-valor="<?= number_format($receita['valor'], 2, ',', '.') ?>"
                            data-vencimento="<?= $receita['data_vencimento'] ?>"
                            data-status="<?= $receita['status'] ?>"
                            data-parcelado="<?= $receita['parcelado'] ?>"
                            data-valor-recebido="<?= number_format($receita['valor_recebido'], 2, ',', '.') ?>"
                            data-data-recebimento="<?= $receita['data_recebimento'] ?>"
                            data-numero-parcelas="<?= $receita['numero_parcelas'] ?>"
                            data-total-parcelas="<?= $receita['total_parcelas'] ?>"
                            data-recorrente="<?= $receita['recorrente'] ?>"
                            data-escopo="<?= htmlspecialchars($receita['escopo_edicao'] ?? 'somente', ENT_QUOTES) ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>

                        <!-- Botão Excluir -->
                        <button class="btn-excluir-receita"
                            data-id="<?= $receita['id_receita'] ?>"
                            data-descricao="<?= htmlspecialchars($receita['descricao'], ENT_QUOTES) ?>"
                            data-valor="<?= number_format($receita['valor'], 2, ',', '.') ?>"
                            data-parcelado="<?= $receita['parcelado'] ?>"
                            data-numero-parcelas="<?= $receita['numero_parcelas'] ?>"
                            data-total-parcelas="<?= $receita['total_parcelas'] ?>">
                            <i class="fas fa-trash-alt"></i> Excluir
                        </button>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>



<!-- scripts -->

<script src="/assets/js/views/receitas-filtros.js"></script>
<script src="/assets/js/views/receitas.js"></script>