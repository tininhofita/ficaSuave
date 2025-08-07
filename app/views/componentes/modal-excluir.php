<!-- CSS exclusivo do modal -->
<link rel="stylesheet" href="/assets/css/views/componentes/modal-excluir.css">

<!-- Modal excluir despesa -->
<div id="modal-excluir" class="modal">
    <div class="modal-conteudo">
        <h3 class="titulo-modal-excluir">Deletar despesa?</h3>
        <div class="caixa-excluir">
            <p><strong>Descrição:</strong> <span id="excluir-descricao"></span></p>
            <p><strong>Valor:</strong> R$ <span id="excluir-valor"></span></p>
        </div>
        <div id="aviso-exclusao-parcelada" class="campo-edicao-parcelas" style="display: none; margin: 10px 0;">
            <div class="aviso-edicao">
                <p id="texto-exclusao-parcelada"></p>
            </div>
            <div class="radio-group">
                <label><input type="radio" name="escopo_exclusao" value="somente" checked> Somente esta</label>
                <label><input type="radio" name="escopo_exclusao" value="futuras"> Esta, e as futuras</label>
                <label><input type="radio" name="escopo_exclusao" value="todas"> Todas as despesas, incluindo as passadas</label>
            </div>
        </div>

        <div class="botoes">
            <button type="button" id="cancelar-exclusao" class="btn btn-cancelar-excluir">Cancelar</button>
            <button type="button" id="confirmar-exclusao" class="btn btn-confirmar-excluir">Deletar</button>
        </div>
    </div>
</div>