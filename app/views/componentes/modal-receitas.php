<!-- CSS exclusivo do modal -->
<link rel="stylesheet" href="/assets/css/views/componentes/modal-receitas.css">

<div id="modal-receita" class="modal">
    <div class="modal-conteudo">
        <!-- Header Moderno -->
        <div class="modal-header modal-header-receita">
            <div class="header-content">
                <div class="icon-wrapper">
                    <i class="fas fa-arrow-circle-up"></i>
                </div>
                <div class="header-text">
                    <h3 class="modal-title" id="titulo-modal-receita">Nova Receita</h3>
                    <p class="modal-subtitle">Registre suas entradas financeiras</p>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-receita">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="form-receita"
            class="form-receita"
            action="/receitas/salvar"
            method="POST">

            <!-- Campo oculto pra edição -->
            <input type="hidden" name="id_receita" id="input-id-receita" value="">


            <!-- Descrição e Valor -->
            <div class="grupo">
                <div class="campo-icone-embutido">
                    <i class="fas fa-align-left"></i>
                    <input type="text" id="descricao" name="descricao" required placeholder="Descrição da receita" autocomplete="off">
                </div>

                <div class="campo-icone-embutido">
                    <i class="fas fa-dollar-sign"></i>
                    <input type="text" id="valor_receita" name="valor" required placeholder="Valor da receita" autocomplete="off">
                </div>
            </div>

            <!-- Status e Data -->

            <div class="grupo">
                <div class="campo-icone-embutido">
                    <i class="fas fa-calendar-day"></i>
                    <label for="data_vencimento">Data Prevista:</label>
                    <input type="date" id="data_vencimento" name="data_vencimento">
                </div>

                <div class="campo-icone-embutido">
                    <i class="fas fa-tasks"></i>
                    <select id="status" name="status">
                        <option value="previsto">Previsto</option>
                        <option value="recebido">Recebido</option>
                    </select>
                </div>
            </div>

            <!-- Valor recebido e data recebimento -->
            <div class="grupo grupo-recebimento" id="grupo-recebimento" style="display: none;">
                <div class="campo-icone-embutido">
                    <i class="fas fa-hand-holding-usd"></i>
                    <input type="text" name="valor_recebido" id="input-valor-recebido" placeholder="Valor recebido">
                </div>

                <div class="campo-icone-embutido">
                    <i class="fas fa-calendar-check"></i>
                    <label for="data_recebimento">Data Recebimento:</label>
                    <input type="date" name="data_recebimento" id="input-data-recebimento">
                </div>
            </div>

            <!-- Categoria e Subcategoria -->
            <div class="grupo grupo-categorias-subcategorias">
                <div class="campo-icone-embutido">
                    <i class="fas fa-folder"></i>
                    <select name="id_categoria" id="categoria-receitas">
                        <option value="">Categoria</option>
                        <?php foreach ($categoriasReceitas as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>">
                                <?= htmlspecialchars($cat['nome_categoria'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>



                </div>

                <div class="campo-icone-embutido">
                    <i class="fas fa-folder-open"></i>
                    <select name="id_subcategoria" id="subcategoria-receitas">
                        <option value="">Subcategoria</option>
                        <?php foreach ($subcategorias as $sub): ?>
                            <option value="<?= $sub['id_subcategoria'] ?>"
                                data-categoria="<?= $sub['id_categoria'] ?>">
                                <?= htmlspecialchars($sub['nome_subcategoria'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Conta e Forma -->
            <div class="grupo">
                <div class="campo-icone-embutido">
                    <i class="fas fa-university"></i>
                    <select id="conta" name="id_conta">
                        <option value="">Conta</option>
                        <?php foreach ($contas as $conta): ?>
                            <option value="<?= $conta['id_conta'] ?>"><?= $conta['nome_conta'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo-icone-embutido">
                    <i class="fas fa-exchange-alt"></i>
                    <select name="id_forma_transacao" id="forma-transacao-select">
                        <option value="">Forma</option>
                        <?php foreach ($formasReceita as $formaRecebimento): ?>
                            <option value="<?= $formaRecebimento['id_forma_transacao'] ?>"><?= $formaRecebimento['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>



            <!-- Parcelado -->
            <div class="checkbox">
                <label><input type="checkbox" name="parcelado" value="1" id="check-parcelado-receita"> Parcelado</label>
            </div>

            <div class="grupo grupo-parcelas-receitas" style="display: none;">
                <div class="campo-icone-embutido">
                    <i class="fas fa-sort-numeric-up-alt"></i>
                    <input type="number" name="numero_parcelas" placeholder="Parcela Atual">
                </div>

                <div class="campo-icone-embutido">
                    <i class="fas fa-hashtag"></i>
                    <input type="number" name="total_parcelas" placeholder="Total de Parcelas">
                </div>
            </div>

            <!-- Aviso Edição Parcelas -->
            <div id="bloco-edicao-parcelas" class="campo-edicao-parcelas" style="display: none;">
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
                    <textarea id="observacoes" name="observacoes" placeholder="Observações" rows="3"></textarea>
                </div>
            </div>

            <!-- Recorrente -->
            <div class="checkbox">
                <label><input type="checkbox" name="recorrente" value="1"> Recorrente</label>
            </div>

            <!-- Footer com Botões -->
            <div class="modal-footer">
                <button type="button" class="btn btn-cancelar-receita">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn btn-salvar">
                    <i class="fas fa-check"></i>
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>