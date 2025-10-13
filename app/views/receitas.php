<!-- Estilo exclusivo da página (opcional, se não estiver usando via $viewStyle) -->
<link rel="stylesheet" href="/assets/css/views/receitas.css">

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<div class="container">
    <!-- Header da página -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Receitas</h1>
            <p class="page-subtitle">Gerencie suas receitas e mantenha o controle financeiro</p>
        </div>
    </div>

    <!-- Cards de estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon recebido">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <h3 class="stat-card-value" id="card-recebidas">0</h3>
                <p class="stat-card-label">Recebidas</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon previsto">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <h3 class="stat-card-value" id="card-previstas">0</h3>
                <p class="stat-card-label">Previstas</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon atrasado">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <h3 class="stat-card-value" id="card-atrasadas">0</h3>
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
                <h3 class="stat-card-value" id="card-total">R$ 0,00</h3>
                <p class="stat-card-label">Total</p>
            </div>
        </div>
    </div>

    <!-- Conteúdo principal -->
    <div class="main-content">

        <!-- Filtros -->
        <div class="filtros-container">
            <!-- Botão Nova Receita -->
            <div class="filtro">
                <button class="btn-nova-receita btn btn-primary" id="btn-adicionar-receita">
                    <i class="fas fa-plus"></i>
                    Nova Receita
                </button>
            </div>

            <?php
            $formasUsadas = [];
            $contasUsadas = [];
            $categoriasUsadas = [];
            $mesesUsados = [];
            $anosUsados = [];

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

                if (!empty($receita['data_vencimento'])) {
                    $mes = date('n', strtotime($receita['data_vencimento']));
                    $mesesUsados[] = $mes;

                    $ano = date('Y', strtotime($receita['data_vencimento']));
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

            <!-- Filtro Ano -->
            <div class="filtro">
                <label for="filtro-ano">Ano:</label>
                <select id="filtro-ano">
                    <option value="">Todos</option>
                    <?php foreach ($anosUsados as $ano): ?>
                        <option value="<?= $ano ?>" <?= $ano == $anoAtual ? 'selected' : '' ?>>
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
                    <?php foreach ($formasReceita as $forma): ?>
                        <?php if (in_array(strtolower($forma['nome']), $formasUsadas)): ?>
                            <option value="<?= strtolower($forma['nome']) ?>"><?= $forma['nome'] ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (in_array('cartão de crédito', $formasUsadas)): ?>
                        <option value="cartão de crédito">Cartão de Crédito</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Filtro Categoria -->
            <div class="filtro">
                <label for="filtro-categoria">Categoria:</label>
                <select id="filtro-categoria">
                    <option value="">Todas</option>
                    <?php foreach ($categoriasReceitas as $cat): ?>
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
            <table class="tabela-receitas">
                <colgroup>
                    <col> <!-- Status -->
                    <col> <!-- Vencimento -->
                    <col> <!-- Descrição -->
                    <col> <!-- Categoria -->
                    <col> <!-- Subcategoria -->
                    <col> <!-- Conta -->
                    <col> <!-- Tipo de Transação -->
                    <col> <!-- Valor -->
                    <col> <!-- Valor Recebido -->
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
                        <th>Valor Recebido</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receitas as $receita):
                        $formaStr = strtolower(trim($receita['nome_forma'] ?? ''));
                    ?>
                        <?php
                        $vencRaw   = !empty($receita['data_vencimento']) ? date('Y-m-d', strtotime($receita['data_vencimento'])) : '';
                        $criadoRaw = !empty($receita['criado_em'])       ? date('Y-m-d H:i:s', strtotime($receita['criado_em'])) : '';
                        $valorRaw  = number_format((float)$receita['valor'], 2, '.', '');
                        ?>
                        <tr
                            data-vencimento="<?= htmlspecialchars($vencRaw,   ENT_QUOTES) ?>"
                            data-criado="<?= htmlspecialchars($criadoRaw, ENT_QUOTES) ?>"
                            data-valor="<?= htmlspecialchars($valorRaw,  ENT_QUOTES) ?>"
                            data-status="<?= htmlspecialchars(strtolower($receita['status']), ENT_QUOTES) ?>">

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
                                <div class="acoes">
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
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!-- Botão Excluir -->
                                    <button class="btn-excluir-receita"
                                        data-id="<?= $receita['id_receita'] ?>"
                                        data-descricao="<?= htmlspecialchars($receita['descricao'], ENT_QUOTES) ?>"
                                        data-valor="<?= number_format($receita['valor'], 2, ',', '.') ?>"
                                        data-parcelado="<?= $receita['parcelado'] ?>"
                                        data-numero-parcelas="<?= $receita['numero_parcelas'] ?>"
                                        data-total-parcelas="<?= $receita['total_parcelas'] ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<!-- scripts -->
<script src="/assets/js/views/receitas-filtros.js"></script>
<script src="/assets/js/views/receitas.js"></script>