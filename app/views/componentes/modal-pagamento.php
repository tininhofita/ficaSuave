<!-- CSS exclusivo do modal -->
<link rel="stylesheet" href="/assets/css/views/componentes/modal-pagamento.css">

<!-- modal de pagamento -->
<div id="modal-pagamento" class="modal">
    <div class="modal-conteudo">
        <h3 class="titulo-modal-pagamento">Pagar Despesa</h3>
        <form id="form-pagamento" class="form-pagamento">
            <input type="hidden" name="id_despesa" id="pagamento-id">

            <div class="grupo">
                <div class="campo-icone-embutido">
                    <i class="fas fa-align-left"></i>
                    <p><span id="pagamento-descricao"></span></p>
                </div>
                <div class="campo-icone-embutido">
                    <i class="fas fa-dollar-sign"></i>
                    <input type="text" name="valor" id="pagamento-valor" required inputmode="decimal">
                </div>
            </div>

            <div class="grupo">
                <div class="campo-icone-embutido">
                    <i class="fas fa-calendar-check"></i>
                    <label>Data de Pagamento:</label>
                    <input type="date" name="data_pagamento" required>
                </div>

                <div class="campo-icone-embutido">
                    <i class="fas fa-university"></i>
                    <select name="id_conta" id="pagamento-conta" required>
                        <?php foreach ($contas as $c): ?>
                            <option value="<?= $c['id_conta'] ?>"><?= $c['nome_conta'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>


            <button type="submit" class="btn btn-confirmar">Confirmar Pagamento</button>
            <button type="button" class="btn btn-cancelar-pagamento">Cancelar</button>

        </form>
    </div>
</div>