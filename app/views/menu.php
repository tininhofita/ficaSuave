<!-- Estilo exclusivo da página (opcional, se não estiver usando via $viewStyle) -->
<link rel="stylesheet" href="/assets/css/views/menu.css">

<div class="container">
    <!-- ===== HEADER DA PÁGINA ===== -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Dashboard Financeiro</h1>
            <p class="page-subtitle">Visão geral das suas finanças e controle de gastos</p>
        </div>
    </div>

    <!-- ===== ESTATÍSTICAS PRINCIPAIS ===== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon saldo">
                    <i class="ph ph-wallet"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-value">R$ <?= number_format($saldoFavorita, 2, ',', '.') ?></p>
                <p class="stat-card-label">Saldo Atual</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon receita">
                    <i class="ph ph-trend-up"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-value">R$ <?= number_format($receitasRecebidas, 2, ',', '.') ?></p>
                <p class="stat-card-label">Receitas Recebidas</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon despesa">
                    <i class="ph ph-trend-down"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-value">R$ <?= number_format($totalPagos, 2, ',', '.') ?></p>
                <p class="stat-card-label">Despesas Pagas</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon cartao">
                    <i class="ph ph-credit-card"></i>
                </div>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-value">R$ <?= number_format($totalCartao, 2, ',', '.') ?></p>
                <p class="stat-card-label">Gastos em Cartão</p>
            </div>
        </div>
    </div>

    <!-- ===== CONTEÚDO PRINCIPAL ===== -->
    <div class="main-content">
        <!-- ===== CARDS SECUNDÁRIOS ===== -->
        <div class="secondary-cards">
            <div class="card receita">
                <i class="ph ph-clock"></i>
                <h3>Receitas Pendentes</h3>
                <p>R$ <?= number_format($receitasPendentes, 2, ',', '.') ?></p>
            </div>
            <div class="card despesa">
                <i class="ph ph-clock"></i>
                <h3>Despesas Pendentes</h3>
                <p>R$ <?= number_format($totalPendentes, 2, ',', '.') ?></p>
            </div>
            <div class="card receita">
                <i class="ph ph-chart-line"></i>
                <h3>Saldo do Mês</h3>
                <p>R$ <?= number_format($receitasRecebidas - $totalPagos, 2, ',', '.') ?></p>
            </div>
            <div class="card despesa">
                <i class="ph ph-warning-circle"></i>
                <h3>Despesas Atrasadas</h3>
                <p>R$ <?= number_format($totalPendentes, 2, ',', '.') ?></p>
            </div>
        </div>

        <section class="container-filtros" style="display: none;">
            <div class="filtros">
                <h3>Status</h3>
                <div class="botoes-status">
                    <button class="btn" data-status="pagos">Pagos</button>
                    <button class="btn ativo" data-status="pendentes">Pendentes</button>
                </div>
            </div>
            <div class="filtros">
                <h3>Ano</h3>
                <div>
                    <button class="btn ativo" data-ano="2021">2021</button>
                    <button class="btn" data-ano="2022">2022</button>
                    <button class="btn" data-ano="2023">2023</button>
                    <button class="btn" data-ano="2024">2024</button>
                    <button class="btn" data-ano="2025">2025</button>
                    <button class="btn" data-ano="2026">2026</button>
                    <button class="btn" data-ano="2027">2027</button>
                    <button class="btn" data-ano="2028">2028</button>
                    <button class="btn" data-ano="2029">2029</button>
                    <button class="btn" data-ano="2030">2030</button>
                </div>
            </div>
            <div class="filtros">
                <h3>Mês</h3>
                <div class="botoes-mes">
                    <button class="btn ativo" data-mes="01">Jan</button>
                    <button class="btn" data-mes="02">Fev</button>
                    <button class="btn" data-mes="03">Mar</button>
                    <button class="btn" data-mes="04">Abr</button>
                    <button class="btn" data-mes="05">Mai</button>
                    <button class="btn" data-mes="06">Jun</button>
                    <button class="btn" data-mes="07">Jul</button>
                    <button class="btn" data-mes="08">Ago</button>
                    <button class="btn" data-mes="09">Set</button>
                    <button class="btn" data-mes="10">Out</button>
                    <button class="btn" data-mes="11">Nov</button>
                    <button class="btn" data-mes="12">Dez</button>
                </div>
            </div>
        </section>

        <!-- ===== ANÁLISE POR CATEGORIAS ===== -->
        <div class="analysis-section">
            <h2 class="section-title">Análise por Categorias</h2>

            <div class="tables-grid">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ph ph-trend-up"></i> Receitas por Categoria</h4>
                    </div>
                    <div class="table-content">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th>Valor</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listarReceitasPorCategoria as $linha): ?>
                                    <tr>
                                        <td><?= $linha['nome_categoria'] ?></td>
                                        <td>R$ <?= number_format($linha['total'], 2, ',', '.') ?></td>
                                        <td><?= number_format($linha['percentual'], 0) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ph ph-trend-down"></i> Despesas por Categoria</h4>
                    </div>
                    <div class="table-content">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th>Valor</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gastosCategorias as $linha):
                                    $idCat = $linha['id_categoria'];
                                    $subcats = $gastosSubcatsPorCat[$idCat] ?? [];
                                    $json   = htmlspecialchars(json_encode($subcats, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
                                ?>
                                    <tr class="categoria-row" data-subcats='<?= $json ?>'>
                                        <td><?= $linha['nome_categoria'] ?></td>
                                        <td>R$ <?= number_format($linha['total'], 2, ',', '.') ?></td>
                                        <td><?= number_format($linha['percentual'], 0) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ph ph-credit-card"></i> Gastos por Cartão</h4>
                    </div>
                    <div class="table-content">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Cartão</th>
                                    <th>Valor</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gastosCartoes as $linha): ?>
                                    <tr>
                                        <td><?= $linha['nome_cartao'] ?></td>
                                        <td>R$ <?= number_format($linha['total'], 2, ',', '.') ?></td>
                                        <td><?= number_format($linha['percentual'], 0) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== GRÁFICOS ===== -->
        <div class="charts-section">
            <h2 class="section-title">Evolução Mensal</h2>
            <div class="chart-container">
                <canvas id="receitas-despesas"
                    data-receitas='<?= json_encode($receitasPorMes, JSON_NUMERIC_CHECK) ?>'
                    data-despesas='<?= json_encode($despesasPorMes, JSON_NUMERIC_CHECK) ?>'></canvas>
            </div>
        </div>
    </div>

    <!-- container do tooltip -->
    <div id="tooltip" class="tooltip" style="display:none; position:absolute; z-index:1000;">
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/assets/js/views/menu.js"></script>