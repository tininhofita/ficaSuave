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

    <!-- ===== FILTROS DE MÊS E ANO ===== -->
    <section class="container-filtros">
        <div class="filtros">
            <h3>Ano</h3>
            <div>
                <?php
                $anoAtual = $anoAtual ?? date('Y');
                for ($ano = 2020; $ano <= 2030; $ano++):
                    $ativo = ($ano == $anoAtual) ? 'ativo' : '';
                ?>
                    <button class="btn <?= $ativo ?>" data-ano="<?= $ano ?>"><?= $ano ?></button>
                <?php endfor; ?>
            </div>
        </div>
        <div class="filtros">
            <h3>Mês</h3>
            <div class="botoes-mes">
                <?php
                $mesAtual = $mesAtual ?? date('n');
                $meses = [
                    1 => 'Jan',
                    2 => 'Fev',
                    3 => 'Mar',
                    4 => 'Abr',
                    5 => 'Mai',
                    6 => 'Jun',
                    7 => 'Jul',
                    8 => 'Ago',
                    9 => 'Set',
                    10 => 'Out',
                    11 => 'Nov',
                    12 => 'Dez'
                ];
                foreach ($meses as $num => $nome):
                    $ativo = ($num == $mesAtual) ? 'ativo' : '';
                    $mesFormatado = str_pad($num, 2, '0', STR_PAD_LEFT);
                ?>
                    <button class="btn <?= $ativo ?>" data-mes="<?= $mesFormatado ?>"><?= $nome ?></button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== CONTEÚDO PRINCIPAL ===== -->
    <div class="main-content">
        <!-- ===== RESUMO FINANCEIRO ===== -->
        <div class="resumo-section" id="resumo-financeiro">
            <h2 class="section-title">Resumo Financeiro</h2>
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
                    <i class="ph ph-credit-card"></i>
                    <h3>Cartão Pendente</h3>
                    <p>R$ <?= number_format($faturasPendentes, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- ===== KPIs FINANCEIROS ===== -->
        <div class="kpis-section" id="kpis-financeiros">
            <h2 class="section-title">Indicadores Financeiros</h2>
            <div class="kpis-grid">
                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon taxa-poupanca">
                            <i class="ph ph-piggy-bank"></i>
                        </div>
                        <div class="kpi-trend">
                            <?php
                            $receitasAtual = $receitasRecebidas;
                            $receitasAnterior = $receitasMesAnterior;
                            $variacaoReceitas = $receitasAnterior > 0 ? (($receitasAtual - $receitasAnterior) / $receitasAnterior) * 100 : 0;
                            $tendenciaReceitas = $variacaoReceitas >= 0 ? 'up' : 'down';
                            $corTendencia = $tendenciaReceitas === 'up' ? 'verde' : 'vermelho';
                            $iconeTendencia = $tendenciaReceitas === 'up' ? '↗️' : '↘️';
                            ?>
                            <span class="tendencia <?= $corTendencia ?>">
                                <?= $iconeTendencia ?> <?= number_format(abs($variacaoReceitas), 1) ?>%
                            </span>
                        </div>
                    </div>
                    <div class="kpi-content">
                        <h3>Taxa de Poupança</h3>
                        <p class="kpi-value <?= $taxaPoupanca >= 0 ? 'positivo' : 'negativo' ?>">
                            <?= $taxaPoupanca >= 0 ? '+' : '' ?><?= number_format($taxaPoupanca, 1) ?>%
                        </p>
                        <p class="kpi-description">
                            <?php if ($taxaPoupanca >= 20): ?>
                                Excelente! Você está poupando bem
                            <?php elseif ($taxaPoupanca >= 10): ?>
                                Bom! Continue assim
                            <?php elseif ($taxaPoupanca >= 0): ?>
                                Você está no azul
                            <?php else: ?>
                                Atenção: gastos acima da renda
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon endividamento">
                            <i class="ph ph-credit-card"></i>
                        </div>
                        <div class="kpi-trend">
                            <?php
                            $cartaoAtual = $totalCartao;
                            $cartaoAnterior = $gastosCartaoMesAnterior;
                            $variacaoCartao = $cartaoAnterior > 0 ? (($cartaoAtual - $cartaoAnterior) / $cartaoAnterior) * 100 : 0;
                            $tendenciaCartao = $variacaoCartao >= 0 ? 'up' : 'down';
                            $corTendenciaCartao = $tendenciaCartao === 'down' ? 'verde' : 'vermelho';
                            $iconeTendenciaCartao = $tendenciaCartao === 'up' ? '↗️' : '↘️';
                            ?>
                            <span class="tendencia <?= $corTendenciaCartao ?>">
                                <?= $iconeTendenciaCartao ?> <?= number_format(abs($variacaoCartao), 1) ?>%
                            </span>
                        </div>
                    </div>
                    <div class="kpi-content">
                        <h3>Endividamento</h3>
                        <p class="kpi-value <?= $taxaEndividamento <= 30 ? 'positivo' : 'negativo' ?>">
                            <?= number_format($taxaEndividamento, 1) ?>%
                        </p>
                        <p class="kpi-description">
                            <?php if ($taxaEndividamento <= 30): ?>
                                Saudável! Cartões sob controle
                            <?php elseif ($taxaEndividamento <= 50): ?>
                                Atenção: limite recomendado
                            <?php else: ?>
                                Crítico: endividamento alto
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon liquidez">
                            <i class="ph ph-coins"></i>
                        </div>
                        <div class="kpi-trend">
                            <?php
                            $despesasAtual = $totalPagos;
                            $despesasAnterior = $despesasPagasMesAnterior;
                            $variacaoDespesas = $despesasAnterior > 0 ? (($despesasAtual - $despesasAnterior) / $despesasAnterior) * 100 : 0;
                            $tendenciaDespesas = $variacaoDespesas >= 0 ? 'up' : 'down';
                            $corTendenciaDespesas = $tendenciaDespesas === 'down' ? 'verde' : 'vermelho';
                            $iconeTendenciaDespesas = $tendenciaDespesas === 'up' ? '↗️' : '↘️';
                            ?>
                            <span class="tendencia <?= $corTendenciaDespesas ?>">
                                <?= $iconeTendenciaDespesas ?> <?= number_format(abs($variacaoDespesas), 1) ?>%
                            </span>
                        </div>
                    </div>
                    <div class="kpi-content">
                        <h3>Liquidez</h3>
                        <p class="kpi-value <?= $liquidez >= 3 ? 'positivo' : ($liquidez >= 1 ? 'neutro' : 'negativo') ?>">
                            <?= number_format($liquidez, 1) ?>x
                        </p>
                        <p class="kpi-description">
                            <?php if ($liquidez >= 3): ?>
                                Excelente! Reserva robusta
                            <?php elseif ($liquidez >= 1): ?>
                                Adequada para emergências
                            <?php else: ?>
                                Baixa: aumente a reserva
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-header">
                        <div class="kpi-icon eficiencia">
                            <i class="ph ph-check-circle"></i>
                        </div>
                        <div class="kpi-trend">
                            <span class="tendencia neutro">
                                📊 <?= number_format($eficienciaPagamentos, 1) ?>%
                            </span>
                        </div>
                    </div>
                    <div class="kpi-content">
                        <h3>Eficiência</h3>
                        <p class="kpi-value <?= $eficienciaPagamentos >= 80 ? 'positivo' : ($eficienciaPagamentos >= 60 ? 'neutro' : 'negativo') ?>">
                            <?= number_format($eficienciaPagamentos, 1) ?>%
                        </p>
                        <p class="kpi-description">
                            <?php if ($eficienciaPagamentos >= 80): ?>
                                Ótimo controle de pagamentos
                            <?php elseif ($eficienciaPagamentos >= 60): ?>
                                Controle regular de pagamentos
                            <?php else: ?>
                                Muitos pagamentos pendentes
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== SCORE FINANCEIRO ===== -->
        <div class="score-section" id="score-financeiro">
            <h2 class="section-title">Score Financeiro</h2>
            <div class="score-container">
                <div class="score-card">
                    <div class="score-circle <?= $scoreFinanceiro['cor'] ?>">
                        <div class="score-value"><?= $scoreFinanceiro['score'] ?></div>
                        <div class="score-max">/100</div>
                    </div>
                    <div class="score-info">
                        <h3>Classificação: <?= $scoreFinanceiro['classificacao'] ?></h3>
                        <p>Seu score baseado em 4 fatores principais</p>
                    </div>
                </div>

                <div class="score-factors">
                    <?php foreach ($scoreFinanceiro['fatores'] as $fator): ?>
                        <div class="factor-item">
                            <span class="factor-name"><?= $fator['nome'] ?></span>
                            <div class="factor-bar">
                                <div class="factor-fill" style="width: <?= ($fator['pontos'] / $fator['max']) * 100 ?>%"></div>
                            </div>
                            <span class="factor-score"><?= $fator['pontos'] ?>/<?= $fator['max'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ===== ALERTAS E RECOMENDAÇÕES ===== -->
        <?php if (!empty($alertasFinanceiros) || !empty($recomendacoesFinanceiras)): ?>
            <div class="alertas-section" id="alertas-recomendacoes">
                <h2 class="section-title">Alertas e Recomendações</h2>

                <!-- Alertas Financeiros -->
                <?php if (!empty($alertasFinanceiros)): ?>
                    <div class="alertas-container">
                        <h3 class="alertas-titulo">
                            <i class="ph ph-warning-circle"></i>
                            Alertas Importantes
                        </h3>
                        <div class="alertas-grid">
                            <?php foreach ($alertasFinanceiros as $alerta): ?>
                                <div class="alerta-card <?= $alerta['tipo'] ?>">
                                    <div class="alerta-header">
                                        <div class="alerta-icon">
                                            <i class="ph <?= $alerta['icone'] ?>"></i>
                                        </div>
                                        <h4><?= $alerta['titulo'] ?></h4>
                                    </div>
                                    <div class="alerta-content">
                                        <p class="alerta-mensagem"><?= $alerta['mensagem'] ?></p>
                                        <p class="alerta-acao"><?= $alerta['acao'] ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recomendações Financeiras -->
                <?php if (!empty($recomendacoesFinanceiras)): ?>
                    <div class="recomendacoes-container">
                        <h3 class="recomendacoes-titulo">
                            <i class="ph ph-lightbulb"></i>
                            Recomendações Inteligentes
                        </h3>
                        <div class="recomendacoes-grid">
                            <?php foreach ($recomendacoesFinanceiras as $recomendacao): ?>
                                <div class="recomendacao-card <?= $recomendacao['tipo'] ?>">
                                    <div class="recomendacao-header">
                                        <div class="recomendacao-icon">
                                            <i class="ph <?= $recomendacao['icone'] ?>"></i>
                                        </div>
                                        <h4><?= $recomendacao['titulo'] ?></h4>
                                    </div>
                                    <div class="recomendacao-content">
                                        <p class="recomendacao-mensagem"><?= $recomendacao['mensagem'] ?></p>
                                        <p class="recomendacao-acao"><?= $recomendacao['acao'] ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ===== ANÁLISE AVANÇADA DE CARTÕES ===== -->
        <div class="cartoes-analysis-section" id="analise-cartoes">
            <h2 class="section-title">Análise Detalhada de Cartões</h2>

            <!-- Cards de Insights de Cartões -->
            <div class="cartoes-insights">
                <div class="insight-card">
                    <div class="insight-header">
                        <div class="insight-icon">
                            <i class="ph ph-trophy"></i>
                        </div>
                        <h3>Cartão Mais Usado</h3>
                    </div>
                    <div class="insight-content">
                        <?php if (!empty($cartaoMaisUsado)): ?>
                            <p class="insight-value"><?= $cartaoMaisUsado['nome_cartao'] ?></p>
                            <p class="insight-detail">
                                <?= $cartaoMaisUsado['bandeira'] ?> •
                                R$ <?= number_format($cartaoMaisUsado['total_gasto'], 2, ',', '.') ?> •
                                <?= $cartaoMaisUsado['qtd_transacoes'] ?> transações
                            </p>
                        <?php else: ?>
                            <p class="insight-value">Sem dados</p>
                            <p class="insight-detail">Nenhum cartão foi usado este mês</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="insight-card">
                    <div class="insight-header">
                        <div class="insight-icon alert">
                            <i class="ph ph-warning-circle"></i>
                        </div>
                        <h3>Cartões em Alerta</h3>
                    </div>
                    <div class="insight-content">
                        <?php if (!empty($cartoesProximosLimite)): ?>
                            <p class="insight-value"><?= count($cartoesProximosLimite) ?> cartão(ões)</p>
                            <p class="insight-detail">
                                <?php foreach ($cartoesProximosLimite as $cartao): ?>
                                    <?= $cartao['nome_cartao'] ?> (<?= number_format($cartao['utilizacao'], 1) ?>%)<br>
                                <?php endforeach; ?>
                            </p>
                        <?php else: ?>
                            <p class="insight-value">0 cartões</p>
                            <p class="insight-detail">Todos os cartões estão seguros</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="insight-card">
                    <div class="insight-header">
                        <div class="insight-icon">
                            <i class="ph ph-credit-card"></i>
                        </div>
                        <h3>Total de Cartões</h3>
                    </div>
                    <div class="insight-content">
                        <p class="insight-value"><?= count($cartoesComLimites) ?> cartões</p>
                        <p class="insight-detail">
                            <?php
                            $totalLimite = array_sum(array_column($cartoesComLimites, 'limite'));
                            $totalDisponivel = array_sum(array_column($cartoesComLimites, 'limite_disponivel'));
                            ?>
                            Limite total: R$ <?= number_format($totalLimite, 2, ',', '.') ?><br>
                            Disponível: R$ <?= number_format($totalDisponivel, 2, ',', '.') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tabela de Cartões com Limites -->
            <div class="cartoes-table-section">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ph ph-credit-card"></i> Cartões e Limites</h4>
                    </div>
                    <div class="table-content">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Cartão</th>
                                    <th>Bandeira</th>
                                    <th>Limite</th>
                                    <th>Gasto do Mês</th>
                                    <th>Disponível</th>
                                    <th>Utilização</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartoesComLimites as $cartao): ?>
                                    <tr>
                                        <td>
                                            <div class="cartao-info">
                                                <div class="cartao-color" style="background-color: <?= $cartao['cor_cartao'] ?>"></div>
                                                <span><?= $cartao['nome_cartao'] ?></span>
                                            </div>
                                        </td>
                                        <td><?= $cartao['bandeira'] ?></td>
                                        <td>R$ <?= number_format($cartao['limite'], 2, ',', '.') ?></td>
                                        <td>R$ <?= number_format($cartao['gasto_mes'], 2, ',', '.') ?></td>
                                        <td>R$ <?= number_format($cartao['limite_disponivel'], 2, ',', '.') ?></td>
                                        <td>
                                            <div class="progress-bar">
                                                <div class="progress-fill"
                                                    style="width: <?= $cartao['utilizacao_percentual'] ?>%; 
                                                            background-color: <?= $cartao['status'] === 'critico' ? '#e74c3c' : ($cartao['status'] === 'atencao' ? '#f39c12' : '#27ae60') ?>">
                                                </div>
                                                <span class="progress-text"><?= number_format($cartao['utilizacao_percentual'], 1) ?>%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= $cartao['status'] ?>">
                                                <?php
                                                switch ($cartao['status']) {
                                                    case 'critico':
                                                        echo '🔴 Crítico';
                                                        break;
                                                    case 'atencao':
                                                        echo '🟡 Atenção';
                                                        break;
                                                    default:
                                                        echo '🟢 Normal';
                                                        break;
                                                }
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Análise por Bandeiras -->
            <?php if (!empty($analiseBandeiras)): ?>
                <div class="bandeiras-section">
                    <div class="table-card">
                        <div class="table-header">
                            <h4><i class="ph ph-chart-pie"></i> Gastos por Bandeira</h4>
                        </div>
                        <div class="table-content">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Bandeira</th>
                                        <th>Cartões</th>
                                        <th>Total Gasto</th>
                                        <th>Ticket Médio</th>
                                        <th>Transações</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analiseBandeiras as $bandeira): ?>
                                        <tr>
                                            <td>
                                                <div class="bandeira-info">
                                                    <span class="bandeira-icon">💳</span>
                                                    <span><?= $bandeira['bandeira'] ?></span>
                                                </div>
                                            </td>
                                            <td><?= $bandeira['qtd_cartoes'] ?></td>
                                            <td>R$ <?= number_format($bandeira['total_gasto'], 2, ',', '.') ?></td>
                                            <td>R$ <?= number_format($bandeira['ticket_medio'], 2, ',', '.') ?></td>
                                            <td><?= $bandeira['qtd_transacoes'] ?></td>
                                            <td><?= number_format($bandeira['percentual'], 1) ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>


        <!-- ===== ANÁLISE POR CATEGORIAS ===== -->
        <div class="analysis-section" id="analise-categorias">
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
                                <?php foreach ($gastosCartoes as $linha):
                                    $idCartao = $linha['id_cartao'];
                                    $categorias = $categoriasPorCartao[$idCartao] ?? [];
                                    $json = htmlspecialchars(json_encode($categorias, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
                                ?>
                                    <tr class="categoria-row cartao-row" data-subcats='<?= $json ?>'>
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

            <!-- ===== NOVA SEÇÃO: FATURAS POR CATEGORIA ===== -->
            <div class="tables-grid" style="margin-top: 2rem;">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ph ph-receipt"></i> Faturas por Categoria</h4>
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
                                <?php foreach ($gastosCategoriasFatura as $linha):
                                    $idCat = $linha['id_categoria'];
                                    $subcats = $gastosSubcatsPorCatFatura[$idCat] ?? [];
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
            </div>
        </div>

        <!-- ===== ANÁLISE VISUAL ===== -->
        <div class="analise-visual-section" id="analise-visual">
            <h2 class="section-title">Análise Visual</h2>

            <!-- Gráfico Principal -->
            <div class="chart-main">
                <h3><i class="ph ph-chart-line"></i> Evolução Mensal</h3>
                <div class="chart-container">
                    <canvas id="receitas-despesas"
                        data-receitas='<?= json_encode($receitasPorMes, JSON_NUMERIC_CHECK) ?>'
                        data-despesas='<?= json_encode($despesasPorMes, JSON_NUMERIC_CHECK) ?>'></canvas>
                </div>
            </div>

            <!-- Gráficos Secundários -->
            <div class="charts-grid">
                <!-- Gráfico de Pizza - Gastos por Categoria -->
                <div class="chart-widget">
                    <div class="chart-header">
                        <h3><i class="ph ph-chart-pie"></i> Distribuição de Gastos</h3>
                        <div class="chart-actions">
                            <button class="chart-action" id="toggle-pizza">
                                <i class="ph ph-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="gastos-pizza"
                            data-categorias='<?= json_encode($gastosCategorias, JSON_NUMERIC_CHECK) ?>'></canvas>
                    </div>
                </div>

                <!-- Gráfico de Barras - Comparação de Cartões -->
                <div class="chart-widget">
                    <div class="chart-header">
                        <h3><i class="ph ph-chart-bar"></i> Gastos por Cartão</h3>
                        <div class="chart-actions">
                            <button class="chart-action" id="toggle-barras">
                                <i class="ph ph-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="cartoes-barras"
                            data-cartoes='<?= json_encode($gastosCartoes, JSON_NUMERIC_CHECK) ?>'></canvas>
                    </div>
                </div>

                <!-- Gráfico de Área - Receitas vs Despesas -->
                <div class="chart-widget">
                    <div class="chart-header">
                        <h3><i class="ph ph-chart-area"></i> Receitas vs Despesas</h3>
                        <div class="chart-actions">
                            <button class="chart-action" id="toggle-area">
                                <i class="ph ph-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="receitas-despesas-area"
                            data-receitas='<?= json_encode($receitasPorMes, JSON_NUMERIC_CHECK) ?>'
                            data-despesas='<?= json_encode($despesasPorMes, JSON_NUMERIC_CHECK) ?>'></canvas>
                    </div>
                </div>

                <!-- Gráfico de Linha - Tendência de Poupança -->
                <div class="chart-widget">
                    <div class="chart-header">
                        <h3><i class="ph ph-trend-up"></i> Tendência de Poupança</h3>
                        <div class="chart-actions">
                            <button class="chart-action" id="toggle-tendencia">
                                <i class="ph ph-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="tendencia-poupanca"
                            data-taxa='<?= json_encode($taxaPoupanca, JSON_NUMERIC_CHECK) ?>'></canvas>
                    </div>
                </div>

                <!-- Gráfico de Radar - Utilização de Cartões -->
                <div class="chart-widget">
                    <div class="chart-header">
                        <h3><i class="ph ph-radar"></i> Utilização de Cartões</h3>
                        <div class="chart-actions">
                            <button class="chart-action" id="toggle-radar">
                                <i class="ph ph-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="utilizacao-cartoes"
                            data-cartoes='<?= json_encode($cartoesComLimites, JSON_NUMERIC_CHECK) ?>'></canvas>
                    </div>
                </div>

                <!-- Gráfico de Doughnut - Gastos por Bandeira -->
                <div class="chart-widget">
                    <div class="chart-header">
                        <h3><i class="ph ph-credit-card"></i> Gastos por Bandeira</h3>
                        <div class="chart-actions">
                            <button class="chart-action" id="toggle-bandeiras">
                                <i class="ph ph-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="gastos-bandeiras"
                            data-bandeiras='<?= json_encode($analiseBandeiras, JSON_NUMERIC_CHECK) ?>'></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MENU DE NAVEGAÇÃO LATERAL ===== -->
    <div class="nav-lateral" id="nav-lateral">
        <div class="nav-toggle" id="nav-toggle">
            <i class="ph ph-list"></i>
        </div>
        <div class="nav-content">
            <h3>Navegação</h3>
            <ul class="nav-links">
                <li><a href="#resumo-financeiro" class="nav-link">📊 Resumo</a></li>
                <li><a href="#kpis-financeiros" class="nav-link">📈 KPIs</a></li>
                <li><a href="#score-financeiro" class="nav-link">🎯 Score</a></li>
                <li><a href="#alertas-recomendacoes" class="nav-link">⚠️ Alertas</a></li>
                <li><a href="#analise-cartoes" class="nav-link">💳 Cartões</a></li>
                <li><a href="#analise-categorias" class="nav-link">📋 Categorias</a></li>
                <li><a href="#analise-visual" class="nav-link">📊 Gráficos</a></li>
            </ul>
        </div>
    </div>

    <!-- ===== BOTÃO VOLTAR AO TOPO ===== -->
    <div class="back-to-top" id="back-to-top">
        <i class="ph ph-arrow-up"></i>
    </div>

    <!-- container do tooltip -->
    <div id="tooltip" class="tooltip" style="display:none; position:absolute; z-index:1000;">
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/assets/js/views/menu.js"></script>