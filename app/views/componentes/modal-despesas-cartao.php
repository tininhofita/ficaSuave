<!-- CSS exclusivo do modal -->
<link rel="stylesheet" href="/assets/css/views/componentes/modal-despesa-cartao.css">

<div id="modal-despesa-cartao" class="modal">
    <div class="modal-conteudo">
        <h3 id="titulo-modal-despesa" class="titulo-modal-despesa">Nova Despesa Cartão</h3>

        <form id="form-despesa-unificada" class="form-despesa">
            <input type="hidden" name="id_despesa" id="input-id-despesa-cartao">
            <input type="hidden" name="tipo_despesa" id="input-tipo-despesa-cartao" value="normal">

            <!-- Descrição e Valor -->
            <div class="grupo">
                <div class="campo-icone-embutido">
                    <i class="fas fa-align-left"></i>
                    <input type="text" name="descricao" required placeholder="Descrição da despesa" autocomplete="off">
                </div>

                <div class="campo-icone-embutido">
                    <i class="fas fa-dollar-sign"></i>
                    <input type="text" id="input-valor-cartao" name="valor" required placeholder="Valor da despesa" autocomplete="off">
                </div>
            </div>

            <!-- 🗓 Status, data de pagamento, valor pago, Vencimento(somente se tipo normal) -->
            <div class="grupo grupo-normal">
                <div class="campo-icone-embutido">
                    <i class="fas fa-calendar-day"></i>
                    <label for="data_pagamento">Vencimento: </label>
                    <input type="date" name="data_vencimento" id="input-vencimento-cartao">
                </div>

                <div class="campo-icone-embutido">
                    <i class="fas fa-tasks"></i>
                    <select name="status" id="select-status-cartao">
                        <option value="pendente">Pendente</option>
                        <option value="pago">Pago</option>
                        <option value="atrasado">Atrasado</option>
                    </select>
                </div>
            </div>

            <div class="grupo grupo-normal">
                <!-- valor pago -->
                <div class="campo-icone-embutido campo-valor-pago" style="display: none;">
                    <i class="fas fa-hand-holding-usd"></i>
                    <input type="text" name="valor_pago" id="input-valor-pago-cartao" placeholder="Valor pago">
                </div>
                <!-- data de pagamento -->
                <div class="campo-icone-embutido campo-data-pagamento" style="display: none;">
                    <i class="fas fa-calendar-check"></i>
                    <label for="data_pagamento">Pagamento: </label>
                    <input type="date" name="data_pagamento" id="input-data-pagamento-cartao">
                </div>
            </div>

            <!-- Categoria/Subcategoria -->
            <div class="grupo">
                <div class="campo-icone-embutido">
                    <i class="fas fa-folder"></i>
                    <select name="categoria" id="select-categoria-cartao">
                        <option value="">Categoria</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>"><?= $cat['nome_categoria'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo-icone-embutido">
                    <i class="fas fa-folder-open"></i>
                    <select name="subcategoria" id="select-subcategoria-cartao">
                        <option value="">Subcategoria</option>
                        <?php foreach ($subcategorias as $sub): ?>
                            <option value="<?= $sub['id_subcategoria'] ?>" data-categoria="<?= $sub['id_categoria'] ?>">
                                <?= $sub['nome_subcategoria'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Cartão e Fatura (somente se tipo cartao) -->
            <div class="grupo grupo-cartao" style="display: none;">
                <div class="campo-icone-embutido">
                    <i class="fas fa-credit-card"></i>
                    <select name="id_cartao" id="select-cartao">
                        <option value="">Cartão</option>
                        <?php foreach ($cartoes as $c): ?>
                            <option value="<?= $c['id_cartao'] ?>"
                                data-conta="<?= $c['id_conta'] ?>"
                                data-fechamento="<?= $c['dia_fechamento'] ?>"
                                data-vencimento="<?= $c['vencimento_fatura'] ?>">
                                <?= ucwords(strtolower($c['nome_cartao'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <input type="hidden" name="id_conta" id="input-conta-cartao">

                <div class="campo-icone-embutido">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <select name="data_vencimento" id="select-fatura">
                        <option value="">Fatura</option>
                    </select>
                </div>
            </div>

            <!-- 🏦 Conta e Forma (somente tipo normal) -->
            <div class="grupo grupo-normal">
                <div class="campo-icone-embutido">
                    <i class="fas fa-university"></i>
                    <select name="id_conta" id="conta-select-cartao">
                        <option value="">Conta</option>
                        <?php foreach ($contas as $conta): ?>
                            <option value="<?= $conta['id_conta'] ?>"><?= $conta['nome_conta'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo-icone-embutido">
                    <i class="fas fa-exchange-alt"></i>
                    <select name="forma" id="forma-transacao-select-cartao">
                        <option value="">Forma</option>
                        <?php foreach ($formas as $forma): ?>
                            <option value="<?= $forma['id_forma_transacao'] ?>"><?= $forma['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Parcelado -->
            <div class="checkbox">
                <label><input type="checkbox" name="parcelado" id="check-parcelado-cartao"> Parcelado</label>
            </div>

            <div class="grupo grupo-parcelas-cartao" style="display: none;">
                <div class="campo-icone-embutido">
                    <i class="fas fa-sort-numeric-up-alt"></i>
                    <input type="number" name="numero_parcelas" placeholder="Parcela Atual">
                </div>

                <div class="campo-icone-embutido">
                    <i class="fas fa-hashtag"></i>
                    <input type="number" name="total_parcelas" placeholder="Total de Parcelas">
                </div>
            </div>

            <!-- 📢 Aviso Edição Parcelas -->
            <div id="bloco-edicao-parcelas-cartao" class="campo-edicao-parcelas" style="display: none;">
                <div class="aviso-edicao">
                    <i class="fas fa-exclamation-triangle"></i>
                    <label id="texto-edicao-parcelas">Atenção! Esta é uma despesa repetida. Você deseja:</label>
                </div>

                <div class="radio-group">
                    <label><input type="radio" name="escopo_edicao" value="somente" checked> Editar somente esta</label>
                    <label><input type="radio" name="escopo_edicao" value="futuras"> Editar esta, e as futuras</label>
                    <label><input type="radio" name="escopo_edicao" value="todas"> Editar todas (incluindo pagas)</label>
                </div>
            </div>

            <!-- Observações -->
            <div class="grupo">
                <div class="campo-icone-embutido">
                    <i class="fas fa-comment-alt"></i>
                    <textarea name="observacoes" placeholder="Observações"></textarea>
                </div>
            </div>

            <!-- Recorrente -->
            <div class="checkbox">
                <label><input type="checkbox" name="recorrente" id="check-recorrente"> Despesa Recorrente</label>
            </div>



            <!-- Botões -->
            <button type="submit" class="btn btn-salvar"><i class="fas fa-check-circle"></i>Salvar</button>
            <button type="button" class="btn btn-cancelar"><i class="fas fa-times-circle"></i>Cancelar</button>
        </form>
    </div>
</div>