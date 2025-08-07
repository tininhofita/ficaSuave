<!-- CSS exclusivo do modal -->
<link rel="stylesheet" href="/assets/css/views/componentes/modal-excluir-receita.css">

<!-- Modal excluir receita -->
<div id="modal-excluir-receita" class="modal">
    <div class="modal-conteudo">
        <h3 class="titulo-modal-excluir-receita">Deletar receita?</h3>
        <div class="caixa-excluir">
            <p><strong>Descrição:</strong>
                <span id="excluir-descricao-receita"></span>
            </p>
            <p><strong>Valor:</strong> R$
                <span id="excluir-valor-receita"></span>
            </p>
        </div>

        <div id="aviso-exclusao-parcelada-receita" class="campo-edicao-parcelas" style="display:none">
            <div class="aviso-edicao">
                <p id="texto-exclusao-parcelada-receita"></p>
            </div>
            <div class="radio-group">
                <label><input type="radio" name="escopo_exclusao" value="somente" checked> Somente esta</label>
                <label><input type="radio" name="escopo_exclusao" value="futuras"> Esta, e as futuras</label>
                <label><input type="radio" name="escopo_exclusao" value="todas"> Todas as despesas, incluindo as passadas</label>
            </div>
        </div>

        <div class="botoes">
            <button id="cancelar-exclusao-receita" class="btn btn-cancelar-excluir">Cancelar</button>
            <button id="confirmar-exclusao-receita" class="btn btn-confirmar-excluir">Deletar</button>
        </div>
    </div>
</div>