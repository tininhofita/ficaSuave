// modal-despesas.js - Modal de despesas normais
document.addEventListener("DOMContentLoaded", () => {
  // === Elementos principais ===
  const modal = document.getElementById("modal-despesa");
  const form = document.getElementById("form-despesa");
  const tituloModal = document.getElementById("titulo-modal-despesa");
  const tipoInput = document.getElementById("input-tipo-despesa");
  const campoId = document.getElementById("input-id-despesa");

  const valorInput = document.getElementById("input-valor");
  const vencimentoInput = document.getElementById("input-vencimento");

  const categoriaSelect = document.getElementById("select-categoria");
  const subcategoriaSelect = document.getElementById("select-subcategoria");
  const statusSelect = document.getElementById("select-status");
  const inputValorPago = document.getElementById("input-valor-pago");
  const inputDataPagamento = document.getElementById("input-data-pagamento");
  const formaSelect = document.getElementById("forma-transacao-select");

  const checkboxParcelado = document.getElementById("check-parcelado");
  const grupoParcelas = document.querySelector(".grupo-parcelas");
  const blocoEdicao = document.getElementById("bloco-edicao-parcelas");

  // === Função de reset (só para 'novo') ===
  function resetCampos() {
    form.reset();
    valorInput.removeAttribute("data-raw");
    checkboxParcelado.checked = false;
    grupoParcelas.style.display = "none";
    blocoEdicao.style.display = "none";
  }

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

  // === Status → mostrar campos de pagamento ===
  statusSelect.addEventListener("change", () => {
    const pago = statusSelect.value === "pago";
    document.querySelector(".campo-data-pagamento").style.display = pago
      ? "flex"
      : "none";
    document.querySelector(".campo-valor-pago").style.display = pago
      ? "flex"
      : "none";
    if (pago) {
      inputValorPago.value = valorInput.value;
      inputValorPago.setAttribute(
        "data-raw",
        valorInput.getAttribute("data-raw") || "0.00"
      );
      inputDataPagamento.value = vencimentoInput.value;
    } else {
      inputValorPago.value = "";
      inputValorPago.removeAttribute("data-raw");
      inputDataPagamento.value = "";
    }
  });
  statusSelect.dispatchEvent(new Event("change"));

  // === Função para abrir modal de despesa normal ===
  function abrirModalDespesaNormal(isEdit = false) {
    if (!isEdit) {
      resetCampos();
      campoId.value = "";
      formaSelect.value = "";
    }

    tipoInput.value = "normal";

    // Ajusta título no header
    const modalTitle = document.querySelector(".modal-title");
    const modalSubtitle = document.querySelector(".modal-subtitle");

    if (isEdit) {
      modalTitle.textContent = "Editar Despesa";
      modalSubtitle.textContent = "Atualize os dados da despesa";
    } else {
      modalTitle.textContent = "Nova Despesa";
      modalSubtitle.textContent = "Preencha os dados da despesa";
    }

    // Para despesas normais, oculta a opção de cartão de crédito (ID 2)
    formaSelect.querySelectorAll("option").forEach((option) => {
      if (option.value === "2") {
        option.style.display = "none";
      } else {
        option.style.display = "block";
      }
    });
  }

  // === Abrir modal (novo) - APENAS despesas normais ===
  document.querySelectorAll(".btn-nova-despesa").forEach((btn) => {
    btn.addEventListener("click", () => {
      const tipo = btn.dataset.tipo || "normal";

      // VALIDAÇÃO: Só processa se for despesa normal
      if (tipo !== "normal") {
        console.warn("Modal de despesas normais: Tipo não suportado:", tipo);
        return;
      }

      abrirModalDespesaNormal(false);
      modal.classList.add("exibir-modal");
    });
  });

  // === Fechar modal ===
  // Botão de fechar no header
  document
    .getElementById("fecharModalDespesa")
    .addEventListener("click", () => {
      modal.classList.remove("exibir-modal");
    });

  // Botão cancelar no footer
  document
    .getElementById("cancelarModalDespesa")
    .addEventListener("click", () => {
      modal.classList.remove("exibir-modal");
    });

  // Fechar clicando fora do modal
  modal.addEventListener("click", (e) => {
    if (!e.target.closest(".modal-conteudo"))
      modal.classList.remove("exibir-modal");
  });

  // === Subcategoria dinâmica ===
  categoriaSelect.addEventListener("change", () => {
    const cat = categoriaSelect.value;
    subcategoriaSelect.querySelectorAll("option").forEach((opt) => {
      const match = opt.dataset.categoria === cat;
      opt.hidden = !match && opt.value !== "";
      if (!match) opt.selected = false;
    });
  });

  // === Parcelado ===
  checkboxParcelado.addEventListener("change", () => {
    grupoParcelas.style.display = checkboxParcelado.checked ? "flex" : "none";
  });

  // === Botões de editar - APENAS despesas normais ===
  document.addEventListener(
    "click",
    (e) => {
      if (e.target.closest(".btn-editar-despesa")) {
        const btn = e.target.closest(".btn-editar-despesa");
        const isCartao = btn.dataset.isCartao === "1";

        // VALIDAÇÃO: Só processa se for despesa normal
        if (isCartao) {
          console.warn(
            "Modal de despesas normais: Não suporta edição de cartão de crédito"
          );
          return;
        }

        // Se chegou aqui, é despesa normal - processar
        e.preventDefault();
        e.stopPropagation();

        console.log("Editando despesa normal:", btn.dataset.id);

        abrirModalDespesaNormal(true);
        modal.classList.add("exibir-modal");

        // campos comuns
        campoId.value = btn.dataset.id || btn.getAttribute("data-id");
        document.querySelector('[name="descricao"]').value =
          btn.dataset.descricao;
        valorInput.value = btn.dataset.valor;
        valorInput.setAttribute(
          "data-raw",
          btn.dataset.valor.replace(/\./g, "").replace(",", ".")
        );

        statusSelect.value = btn.dataset.status;
        statusSelect.dispatchEvent(new Event("change"));
        if (btn.dataset.status === "pago") {
          inputValorPago.value = btn.dataset.valorPago;
          inputValorPago.setAttribute(
            "data-raw",
            btn.dataset.valorPago.replace(/\./g, "").replace(",", ".")
          );
          inputDataPagamento.value =
            btn.dataset.dataPagamento || btn.dataset.vencimento;
        }

        // categoria / subcategoria
        categoriaSelect.value = btn.dataset.categoriaId;
        categoriaSelect.dispatchEvent(new Event("change"));
        subcategoriaSelect.value = btn.dataset.subcategoria;

        // data de vencimento
        vencimentoInput.value = btn.dataset.vencimento;

        // parcelas e observações
        checkboxParcelado.checked = btn.dataset.parcelado === "1";
        grupoParcelas.style.display = checkboxParcelado.checked
          ? "flex"
          : "none";
        form.querySelector('[name="numero_parcelas"]').value =
          btn.dataset.numeroParcelas;
        form.querySelector('[name="total_parcelas"]').value =
          btn.dataset.totalParcelas;

        // observações
        form.querySelector('[name="observacoes"]').value =
          btn.dataset.observacoes || "";

        // conta normal
        document.getElementById("conta-select").value = btn.dataset.contaId;

        // forma de transação
        formaSelect.value = btn.dataset.formaId;
      }
    },
    true
  ); // true = capture phase para ter prioridade

  // === SUBMIT - APENAS despesas normais ===
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    // 1) Status
    formData.set("status", statusSelect.value);

    // 2) Valor
    const rawValor = valorInput.getAttribute("data-raw") || "0.00";
    formData.set("valor", rawValor);

    // 3) Valor pago / data pagamento (se pago)
    if (statusSelect.value === "pago") {
      const rawValorPago = inputValorPago.getAttribute("data-raw") || "0.00";
      formData.set("valor_pago", rawValorPago);
      formData.set("data_pagamento", inputDataPagamento.value);
    } else {
      formData.delete("valor_pago");
      formData.delete("data_pagamento");
    }

    // 4) Forma de transação (pega do select)
    formData.set("forma", formaSelect.value);

    // 5) Conta e vencimento
    formData.set("id_conta", document.getElementById("conta-select").value);
    formData.set("data_vencimento", vencimentoInput.value);

    // 6) Flags adicionais
    formData.set("parcelado", checkboxParcelado.checked ? "1" : "0");
    formData.set(
      "recorrente",
      document.getElementById("check-recorrente").checked ? "1" : "0"
    );
    const esc = form.querySelector("input[name='escopo_edicao']:checked");
    if (esc) formData.set("modo_edicao", esc.value);

    // 7) Log para você verificar
    console.log("📬 Dados que vamos enviar:");
    for (let [key, val] of formData.entries()) {
      console.log(`   ${key} =`, val);
    }

    // 8) Envia
    const isEdicao = campoId.value !== "";
    const url = isEdicao ? "/despesas/atualizar" : "/despesas/salvar";

    try {
      const res = await fetch(url, { method: "POST", body: formData });
      const text = await res.text();
      const start = text.indexOf("{"),
        end = text.lastIndexOf("}");
      const json = JSON.parse(
        start > -1 && end > -1 ? text.slice(start, end + 1) : text
      );
      if (json.sucesso) {
        alert(isEdicao ? "Despesa atualizada!" : "Despesa salva!");
        location.reload();
      } else {
        alert(json.erro || "Erro ao salvar despesa.");
      }
    } catch (err) {
      console.error(err);
      alert("Erro de conexão.");
    }
  });
});
