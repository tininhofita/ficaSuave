<!-- Estilo exclusivo da página (opcional, se não estiver usando via $viewStyle) -->
<link rel="stylesheet" href="/assets/css/views/bancos.css">

<div class="bancos-container">
    <!-- Header Padrão -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-title">
                <h1><i class="fas fa-university"></i> Contas Bancárias</h1>
                <p>Gerencie suas contas bancárias e movimentações</p>
            </div>
            <div class="header-actions">
                <button id="btn-transferencia" class="btn btn-secondary">
                    <i class="fas fa-exchange-alt"></i> Transferir
                </button>
                <button id="btn-adicionar-banco" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nova Conta
                </button>
            </div>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="resumo-cards">
        <div class="resumo-card">
            <div class="card-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="card-content">
                <h3>Saldo Total</h3>
                <p class="card-value">R$ <span id="saldo-total">0,00</span></p>
            </div>
        </div>
        <div class="resumo-card">
            <div class="card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="card-content">
                <h3>Contas Ativas</h3>
                <p class="card-value"><span id="contas-ativas">0</span></p>
            </div>
        </div>
        <div class="resumo-card">
            <div class="card-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="card-content">
                <h3>Conta Favorita</h3>
                <p class="card-value"><span id="conta-favorita">-</span></p>
            </div>
        </div>
    </div>

    <!-- Tabela de Contas -->
    <div class="tabela-container">
        <table class="tabela-bancos">
            <thead>
                <tr>
                    <th>Fav</th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Banco</th>
                    <th>Saldo Inicial</th>
                    <th>Saldo Atual</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $saldoTotal = 0;
                $contasAtivas = 0;
                $contaFavorita = '';
                ?>
                <?php foreach ($bancos as $banco): ?>
                    <?php
                    $saldoTotal += (float)$banco['saldo_atual'];
                    if ($banco['ativa']) $contasAtivas++;
                    if (!empty($banco['favorita'])) $contaFavorita = $banco['nome_conta'];
                    ?>
                    <tr data-id="<?= $banco['id_conta'] ?>" class="<?= $banco['ativa'] ? 'conta-ativa' : 'conta-inativa' ?>">
                        <td>
                            <button
                                class="btn-favorita <?= !empty($banco['favorita']) ? 'is-fav' : '' ?>"
                                title="<?= !empty($banco['favorita']) ? 'Conta favorita' : 'Definir como favorita' ?>"
                                aria-label="<?= !empty($banco['favorita']) ? 'Conta favorita' : 'Definir como favorita' ?>"
                                data-id="<?= $banco['id_conta'] ?>">
                                <i class="<?= !empty($banco['favorita']) ? 'fas' : 'far' ?> fa-star"></i>
                            </button>
                        </td>
                        <td>
                            <div class="conta-info">
                                <strong><?= mb_convert_case($banco['nome_conta'], MB_CASE_TITLE, 'UTF-8') ?></strong>
                                <?php if (!empty($banco['favorita'])): ?>
                                    <span class="badge-favorita">Favorita</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge-tipo badge-<?= $banco['tipo'] ?>">
                                <?= mb_convert_case($banco['tipo'], MB_CASE_TITLE, 'UTF-8') ?>
                            </span>
                        </td>
                        <td><?= !empty($banco['banco']) ? $banco['banco'] : '-' ?></td>
                        <td class="valor">R$ <?= number_format((float)$banco['saldo_inicial'], 2, ',', '.') ?></td>
                        <td class="valor">
                            <span class="saldo-atual">R$ <?= number_format((float)$banco['saldo_atual'], 2, ',', '.') ?></span>
                            <?php if ((float)$banco['saldo_atual'] != (float)$banco['saldo_inicial']): ?>
                                <span class="variacao <?= (float)$banco['saldo_atual'] > (float)$banco['saldo_inicial'] ? 'positiva' : 'negativa' ?>">
                                    <?= (float)$banco['saldo_atual'] > (float)$banco['saldo_inicial'] ? '+' : '' ?>
                                    R$ <?= number_format((float)$banco['saldo_atual'] - (float)$banco['saldo_inicial'], 2, ',', '.') ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?= $banco['ativa'] ? 'ativa' : 'inativa' ?>">
                                <?= $banco['ativa'] ? 'Ativa' : 'Inativa' ?>
                            </span>
                        </td>
                        <td>
                            <div class="acoes-buttons">
                                <button class="btn-editar"
                                    data-id="<?= $banco['id_conta'] ?>"
                                    data-nome="<?= $banco['nome_conta'] ?>"
                                    data-tipo="<?= $banco['tipo'] ?>"
                                    data-banco="<?= $banco['banco'] ?>"
                                    data-ativa="<?= $banco['ativa'] ?>"
                                    data-saldo-inicial="<?= number_format((float)$banco['saldo_inicial'], 2, ',', '.') ?>"
                                    data-saldo-atual="<?= number_format((float)$banco['saldo_atual'], 2, ',', '.') ?>"
                                    title="Editar conta">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-transferir"
                                    data-id="<?= $banco['id_conta'] ?>"
                                    data-nome="<?= $banco['nome_conta'] ?>"
                                    data-saldo="<?= number_format((float)$banco['saldo_atual'], 2, ',', '.') ?>"
                                    title="Transferir para esta conta">
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                                <button class="btn-excluir"
                                    data-id="<?= $banco['id_conta'] ?>"
                                    title="Excluir conta">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Script para atualizar cards de resumo -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('saldo-total').textContent = '<?= number_format($saldoTotal, 2, ',', '.') ?>';
            document.getElementById('contas-ativas').textContent = '<?= $contasAtivas ?>';
            document.getElementById('conta-favorita').textContent = '<?= $contaFavorita ?: '-' ?>';
        });
    </script>




    <!-- Modal de Cadastro/Edição -->
    <div id="modal-cadastro-banco" class="modal">
        <div class="modal-content">
            <h3 id="titulo-modal-banco">Cadastro de Conta Bancária</h3>
            <form id="form-cadastro-banco">
                <input type="hidden" name="id_conta" id="id_conta">

                <label for="nome_conta">Nome da Conta:</label>
                <input type="text" name="nome_conta" id="nome_conta" required>

                <label for="tipo">Tipo de Conta:</label>
                <select name="tipo" id="tipo" required>
                    <option value="corrente">Corrente</option>
                    <option value="poupanca">Poupança</option>
                    <option value="salario">Salário</option>
                    <option value="digital">Digital</option>
                    <option value="investimento">Investimento</option>
                    <option value="outro">Outro</option>
                </select>

                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="ativa" id="ativa"> Conta Ativa
                    </label>
                </div>

                <label for="banco">Banco:</label>
                <input type="text" name="banco" id="banco">

                <label for="saldo_inicial">Saldo Inicial:</label>
                <input type="text" name="saldo_inicial" id="saldo_inicial" value="0,00" inputmode="decimal" placeholder="0,00">

                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <button type="button" class="btn btn-secondary btn-cancelar">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Transferência -->
    <div id="modal-transferencia" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-exchange-alt"></i> Transferência entre Contas</h3>
            <form id="form-transferencia">
                <div class="form-row">
                    <div class="form-group">
                        <label for="conta-origem">Conta de Origem:</label>
                        <select name="conta_origem" id="conta-origem" required>
                            <option value="">Selecione a conta</option>
                        </select>
                        <div class="saldo-info">
                            <small>Saldo disponível: <span id="saldo-origem">R$ 0,00</span></small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="conta-destino">Conta de Destino:</label>
                        <select name="conta_destino" id="conta-destino" required>
                            <option value="">Selecione a conta</option>
                        </select>
                        <div class="saldo-info">
                            <small>Saldo atual: <span id="saldo-destino">R$ 0,00</span></small>
                        </div>
                    </div>
                </div>

                <label for="valor-transferencia">Valor da Transferência:</label>
                <input type="text" name="valor_transferencia" id="valor-transferencia" required placeholder="0,00">

                <label for="observacao-transferencia">Observação (opcional):</label>
                <textarea name="observacao" id="observacao-transferencia" placeholder="Descrição da transferência..."></textarea>

                <div class="resumo-transferencia">
                    <div class="resumo-item">
                        <span>Valor:</span>
                        <span id="resumo-valor">R$ 0,00</span>
                    </div>
                    <div class="resumo-item">
                        <span>Taxa:</span>
                        <span id="resumo-taxa">R$ 0,00</span>
                    </div>
                    <div class="resumo-item total">
                        <span>Total:</span>
                        <span id="resumo-total">R$ 0,00</span>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Confirmar Transferência</button>
                    <button type="button" class="btn btn-secondary btn-cancelar-transferencia">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

</div>


<script src="/assets/js/views/bancos.js"></script>