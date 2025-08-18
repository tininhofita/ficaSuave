<!-- Estilo exclusivo da página (opcional, se não estiver usando via $viewStyle) -->
<link rel="stylesheet" href="/assets/css/views/bancos.css">

<div class="container">
    <h2>Contas</h2>

    <button id="btn-adicionar-banco" class="btn">+ Adicionar Banco</button>

    <table class="tabela-bancos">
        <thead>
            <tr>
                <th>Fav</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Saldo Inicial</th>
                <th>Saldo Atual</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bancos as $banco): ?>
                <tr data-id="<?= $banco['id_conta'] ?>">
                    <td>
                        <button
                            class="btn-favorita <?= !empty($banco['favorita']) ? 'is-fav' : '' ?>"
                            title="<?= !empty($banco['favorita']) ? 'Conta favorita' : 'Definir como favorita' ?>"
                            aria-label="<?= !empty($banco['favorita']) ? 'Conta favorita' : 'Definir como favorita' ?>"
                            data-id="<?= $banco['id_conta'] ?>">
                            <i class="<?= !empty($banco['favorita']) ? 'fas' : 'far' ?> fa-star"></i>
                        </button>
                    </td>
                    <td><?= mb_convert_case($banco['nome_conta'], MB_CASE_TITLE, 'UTF-8') ?></td>
                    <td><?= mb_convert_case($banco['tipo'], MB_CASE_TITLE, 'UTF-8') ?></td>
                    <td>R$ <?= number_format((float)$banco['saldo_inicial'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format((float)$banco['saldo_atual'],   2, ',', '.') ?></td>
                    <td><?= $banco['ativa'] ? 'Ativa' : 'Inativa' ?></td>
                    <td>
                        <button class="btn-editar"
                            data-id="<?= $banco['id_conta'] ?>"
                            data-nome="<?= $banco['nome_conta'] ?>"
                            data-tipo="<?= $banco['tipo'] ?>"
                            data-banco="<?= $banco['banco'] ?>"
                            data-ativa="<?= $banco['ativa'] ?>"
                            data-saldo-inicial="<?= number_format((float)$banco['saldo_inicial'], 2, ',', '.') ?>"
                            data-saldo-atual="<?= number_format((float)$banco['saldo_atual'],   2, ',', '.') ?>">Editar</button>

                        <button class="btn-excluir" data-id="<?= $banco['id_conta'] ?>">Excluir</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>




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



                <button type="submit" class="btn">Salvar</button>
                <button type="button" class="btn-cancelar">Cancelar</button>
            </form>
        </div>
    </div>


</div>


<script src="/assets/js/views/bancos.js"></script>