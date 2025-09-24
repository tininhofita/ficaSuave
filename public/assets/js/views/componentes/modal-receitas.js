document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("modal-receita");
  const btnAbrir = document.getElementById("btn-adicionar-receita");
  const btnCancelar = document.querySelector(".btn-cancelar-receita");
  const form = document.getElementById("form-receita");
  const inputIdReceita = document.getElementById("input-id-receita");

  // campos do form
  const descricaoInput = document.getElementById("descricao");
  const valorInput = document.getElementById("valor_receita");
  const vencimentoInput = document.getElementById("data_vencimento");
  const statusSelect = document.getElementById("status");
  const inputValorPago = document.getElementById("input-valor-recebido");
  const dataRecebimentoInput = document.getElementById(
    "input-data-recebimento"
  );
  const categoriaSel = document.getElementById("categoria-receitas");
  const subcategoriaSel = document.getElementById("subcategoria-receitas");
  const parceladoCheckbox = document.getElementById("check-parcelado-receita");
  const grupoReceb = document.getElementById("grupo-recebimento");
  const grupoParcelas = document.querySelector(".grupo-parcelas-receitas");
  const numeroParcInput = grupoParcelas.querySelector(
    'input[name="numero_parcelas"]'
  );
  const totalParcInput = grupoParcelas.querySelector(
    'input[name="total_parcelas"]'
  );
  const blocoEdicaoParcelas = document.getElementById("bloco-edicao-parcelas");
  const escopoRadios = document.getElementsByName("escopo_edicao");
  const observacoesInput = document.getElementById("observacoes");
  const recorrenteCheckbox = form.querySelector('input[name="recorrente"]');

  // === Máscaras de Valor (centavos automáticos) ===
  function aplicarMascaraCentavos(input) {
    input.addEventListener("input", (e) => {
      let valor = e.target.value.replace(/\D/g, "");

      if (valor === "") {
        e.target.value = "";
        e.target.removeAttribute("data-raw");
        return;
      }

      // Converte para centavos automaticamente
      const valorFormatado = (parseInt(valor, 10) / 100).toFixed(2);
      const valorBR = valorFormatado
        .replace(".", ",")
        .replace(/\B(?=(\d{3})+(?!\d))/g, ".");

      e.target.value = valorBR;
      e.target.setAttribute("data-raw", valorFormatado);
    });
  }

  // Aplicar máscara nos campos de valor
  aplicarMascaraCentavos(valorInput);
  if (inputValorPago) {
    aplicarMascaraCentavos(inputValorPago);
  }

  // --- aberturas e fechamentos padrão ---
  btnAbrir?.addEventListener("click", () => {
    prepararModalNovo();
    modal.classList.add("exibir-modal");
  });
  btnCancelar?.addEventListener("click", () =>
    modal.classList.remove("exibir-modal")
  );
  modal?.addEventListener("click", (e) => {
    if (!e.target.closest(".modal-conteudo"))
      modal.classList.remove("exibir-modal");
  });

  // --- botão Editar: popula form e abre modal ---
  document.querySelectorAll(".btn-editar-receita").forEach((btn) => {
    btn.addEventListener("click", () => {
      form.action = "/receitas/atualizar";
      inputIdReceita.value = btn.dataset.id;

      // campos básicos
      descricaoInput.value = btn.dataset.descricao;
      valorInput.value = btn.dataset.valor;
      valorInput.setAttribute("data-raw", btn.dataset.valor.replace(/\D/g, ""));
      vencimentoInput.value = btn.dataset.vencimento;
      statusSelect.value = btn.dataset.status;
      toggleRecebimento();
      const vrBruto = btn.dataset.valorRecebido || "0,00";
      const vrNum =
        parseFloat(vrBruto.replace(/\./g, "").replace(",", ".")) || 0;
      if (vrNum > 0) {
        inputValorPago.value = vrBruto;
        inputValorPago.setAttribute("data-raw", vrNum.toFixed(2));
      } else {
        // se ainda não foi recebido, já preenche com o valor original
        const vBruto = btn.dataset.valor;
        const vNum =
          parseFloat(vBruto.replace(/\./g, "").replace(",", ".")) || 0;
        inputValorPago.value = vBruto;
        inputValorPago.setAttribute("data-raw", vNum.toFixed(2));
      }

      dataRecebimentoInput.value =
        btn.dataset.dataRecebimento || vencimentoInput.value;

      // categorias
      categoriaSel.value = btn.dataset.categoria;
      filtrarSubcategorias();
      subcategoriaSel.value = btn.dataset.subcategoria;

      // conta/forma
      document.getElementById("conta").value = btn.dataset.conta;
      document.getElementById("forma-transacao-select").value =
        btn.dataset.forma;

      // parcelado
      parceladoCheckbox.checked = btn.dataset.parcelado === "1";
      toggleParcelas();
      numeroParcInput.value = btn.dataset.numeroParcelas;
      totalParcInput.value = btn.dataset.totalParcelas;

      // recorrente & escopo — *ordem ajustada*
      recorrenteCheckbox.checked = btn.dataset.recorrente === "1";
      if (btn.dataset.parcelado === "1" || btn.dataset.recorrente === "1") {
        blocoEdicaoParcelas.style.display = "block";
        const escopo = btn.dataset.escopo || "somente";
        escopoRadios.forEach((r) => (r.checked = r.value === escopo));
      } else {
        blocoEdicaoParcelas.style.display = "none";
      }

      observacoesInput.value = btn.dataset.observacoes || "";

      document.getElementById("titulo-modal-receita").textContent =
        "Editar Receita";
      modal.classList.add("exibir-modal");
    });
  });

  // --- show/hide RECEBIMENTO + pré-fill ---
  function toggleRecebimento() {
    if (statusSelect.value === "recebido") {
      grupoReceb.style.display = "flex";

      // só preenche se ainda estiver vazio (evita sobrescrever no editar)
      const raw = parseFloat(inputValorPago.getAttribute("data-raw") || "0");
      if (!inputValorPago.value || raw <= 0) {
        inputValorPago.value = valorInput.value;
        inputValorPago.setAttribute(
          "data-raw",
          valorInput.getAttribute("data-raw") || ""
        );
      }

      if (!dataRecebimentoInput.value) {
        // copia data de vencimento
        dataRecebimentoInput.value = vencimentoInput.value;
      }
    } else {
      grupoReceb.style.display = "none";
      inputValorPago.value = "";
      dataRecebimentoInput.value = "";
    }
  }

  statusSelect.addEventListener("change", toggleRecebimento);

  // --- show/hide PARCELAS ---
  function toggleParcelas() {
    if (parceladoCheckbox.checked) {
      grupoParcelas.style.display = "flex";
    } else {
      grupoParcelas.style.display = "none";
      numeroParcInput.value = "";
      totalParcInput.value = "";
    }
  }
  parceladoCheckbox.addEventListener("change", toggleParcelas);

  // --- filtro SUBCATEGORIAS (já existente) ---
  function filtrarSubcategorias() {
    const catId = categoriaSel.value;
    Array.from(subcategoriaSel.options).forEach((opt) => {
      opt.style.display =
        !opt.value || opt.dataset.categoria === catId ? "" : "none";
    });
    if (subcategoriaSel.selectedOptions[0]?.style.display === "none") {
      subcategoriaSel.value = "";
    }
  }
  categoriaSel.addEventListener("change", filtrarSubcategorias);

  // enviar formulário
  document
    .getElementById("form-receita")
    .addEventListener("submit", async (e) => {
      e.preventDefault();
      const form = e.target;
      const data = new FormData(form);

      const dv = data.get("data_vencimento") || "";
      if (!/^\d{4}-\d{2}-\d{2}$/.test(dv)) {
        alert("Por favor, preencha uma data prevista no formato YYYY-MM-DD.");
        return;
      }

      try {
        const resp = await fetch(form.action, {
          method: form.method,
          body: data,
        });
        const json = await resp.json();
        if (json.sucesso) {
          // exibe mensagem, fecha modal, recarrega lista de receitas
          alert("Receita salva com sucesso!");
          form.reset();
          document
            .getElementById("modal-receita")
            .classList.remove("exibir-modal");
          window.location.reload();
        } else {
          alert("Erro ao salvar. Tenta de novo mais tarde.");
        }
      } catch (err) {
        console.error(err);
        alert("Erro na requisição: " + err.message);
      }
    });

  // prepara o modal para NOVA receita
  function prepararModalNovo() {
    form.action = "/receitas/salvar";
    inputIdReceita.value = "";
    document.getElementById("titulo-modal-receita").textContent =
      "Nova Receita";
    form.reset();
    toggleRecebimento();
    toggleParcelas();
    blocoEdicaoParcelas.style.display = "none";
  }
});
