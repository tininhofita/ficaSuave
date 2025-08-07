<!-- Estilo exclusivo da página (opcional, se não estiver usando via $viewStyle) -->
<link rel="stylesheet" href="/assets/css/views/formas-transacao.css">

<div class="container">
    <button id="btn-adicionar-forma" class="btn">+ Nova Forma de Transação</button>

    <table class="tabela-formas">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Uso</th>
                <th>Ativa</th>
                <th>Padrão</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($formas as $forma): ?>
                <tr>
                    <td><?= $forma['nome'] ?></td>
                    <td><?= ucfirst($forma['tipo']) ?></td>
                    <td><?= ucfirst($forma['uso']) ?></td>
                    <td><?= $forma['ativa'] ? 'Sim' : 'Não' ?></td>
                    <td><?= $forma['padrao'] ? 'Sim' : 'Não' ?></td>
                    <td>
                        <?php if (!$forma['padrao']): ?>
                            <button class="btn-editar"
                                data-id="<?= $forma['id_forma_transacao'] ?>"
                                data-nome="<?= $forma['nome'] ?>"
                                data-tipo="<?= $forma['tipo'] ?>"
                                data-uso="<?= $forma['uso'] ?>"
                                data-ativa="<?= $forma['ativa'] ?>">Editar</button>

                            <button class="btn-excluir" data-id="<?= $forma['id_forma_transacao'] ?>">Excluir</button>
                        <?php else: ?>
                            <button class="btn-editar padrao" data-padrao="1" title="Item padrão do sistema">Editar</button>
                            <button class="btn-excluir padrao" data-padrao="1" title="Item padrão do sistema">Excluir</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>

</div>

<!-- Modal -->
<div id="modal-forma" class="modal">
    <div class="modal-conteudo">
        <h3 id="titulo-modal">Nova Forma de Transação</h3>


        <form id="form-forma-transacao">
            <input type="hidden" name="id_forma_transacao" id="id_forma_transacao">

            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required>

            <label for="tipo">Tipo:</label>
            <select name="tipo" id="tipo" required>
                <option value="dinheiro">Dinheiro</option>
                <option value="cartao">Cartão</option>
                <option value="transferencia">Transferência</option>
                <option value="boleto">Boleto</option>
                <option value="outro">Outro</option>
            </select>

            <label for="uso">Uso:</label>
            <select name="uso" id="uso" required>
                <option value="pagamento">Pagamento</option>
                <option value="recebimento">Recebimento</option>
                <option value="ambos">Ambos</option>
            </select>

            <label>
                <input type="checkbox" name="ativa" id="ativa" checked> Ativa
            </label>

            <button type="submit" class="btn">Salvar</button>
            <button type="button" class="btn-cancelar">Cancelar</button>
        </form>
    </div>
</div>






<script src="/assets/js/views/formas-transacao.js"></script>