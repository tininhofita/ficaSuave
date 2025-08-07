<!-- Estilo exclusivo da página (opcional, se não estiver usando via $viewStyle) -->
<link rel="stylesheet" href="/assets/css/views/menu.css">

<div class="container">
    <section class="container-cards">
        <div class="cards">
            <div class="card saldo">
                <i class="ph ph-wallet"></i>
                <h3>Saldo Atual</h3>
                <p>R$ <?= number_format($saldoFavorita, 2, ',', '.') ?></p>
            </div>

            <div class="card receita">
                <i class="ph ph-trend-up"></i>
                <h3>Receitas</h3>
                <p>R$ 0</p>
            </div>
            <div class="card despesa">
                <i class="ph ph-trend-down"></i>
                <h3>Despesas</h3>
                <p>R$ <?= number_format($TotalDespesasSemCartao, 2, ',', '.') ?></p>
            </div>
            <div class="card cartao">
                <i class="ph ph-credit-card"></i>
                <h3>Cartão de crédito</h3>
                <p>R$ <?= number_format($totalCartao, 2, ',', '.') ?></p>
            </div>
        </div>
        <div class="cards">
            <div class="card receita">
                <i class="ph ph-check-circle"></i>
                <h3>Receitas Recebidas</h3>
                <p>R$ <?= number_format($receitasRecebidas, 2, ',', '.') ?></p>
            </div>
            <div class="card despesa">
                <i class="ph ph-check-circle"></i>
                <h3>Despesas Pagas</h3>
                <p>R$ <?= number_format($totalPagos, 2, ',', '.') ?></p>
            </div>
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
        </div>
    </section>

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

    <section class="container-tabelas">

        <div class="tabelas">
            <div class="receitas">
                <h4>Receitas</h4>
                <table>
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
            <div class="despesas">
                <h4>Despesas</h4>
                <table>
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
            <div class="cartao">
                <h4>Cartões</h4>
                <table>
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
    </section>

    <!-- container do tooltip -->
    <div id="tooltip" class="tooltip" style="display:none; position:absolute; z-index:1000;">

    </div>


    <section class="graficos">
        <div class="receitas-despesas">
            <canvas id="receitas-despesas"
                data-receitas='<?= json_encode($receitasPorMes, JSON_NUMERIC_CHECK) ?>'
                data-despesas='<?= json_encode($despesasPorMes, JSON_NUMERIC_CHECK) ?>'></canvas>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/assets/js/views/menu.js"></script>