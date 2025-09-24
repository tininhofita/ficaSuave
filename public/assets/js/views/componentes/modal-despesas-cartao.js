// despesas-unificadas.js
document.addEventListener("DOMContentLoaded", () => {
  // === Elementos principais ===
  const modal = document.getElementById("modal-despesa-cartao");
  const form = document.getElementById("form-despesa-unificada");
  const tituloModal = document.getElementById("titulo-modal-despesa");
  const tipoInput = document.getElementById("input-tipo-despesa-cartao");
  const campoId = document.getElementById("input-id-despesa-cartao");

  const valorInput = document.getElementById("input-valor-cartao");
  const vencimentoInput = document.getElementById("input-vencimento-cartao");
  const cartaoSelect = document.getElementById("select-cartao");
  const faturaSelect = document.getElementById("select-fatura");
  const contaCartaoInput = document.getElementById("input-conta-cartao");

  const categoriaSelect = document.getElementById("select-categoria-cartao");
  const subcategoriaSelect = document.getElementById(
    "select-subcategoria-cartao"
  );
  const statusSelect = document.getElementById("select-status-cartao");
  const inputValorPago = document.getElementById("input-valor-pago-cartao");
  const inputDataPagamento = document.getElementById(
    "input-data-pagamento-cartao"
  );
  const formaSelect = document.getElementById("forma-transacao-select-cartao");

  const checkboxParcelado = document.getElementById("check-parcelado-cartao");
  const grupoParcelas = document.querySelector(".grupo-parcelas-cartao");
  const blocoEdicao = document.getElementById("bloco-edicao-parcelas-cartao");

  const grupoNormal = document.querySelectorAll(".grupo-normal");
  const grupoCartao = document.querySelectorAll(".grupo-cartao");

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

  // === Tipo (normal vs cartão) ===
  // === Tipo (normal vs cartão) ===
  function toggleTipo(tipo, isEdit = false) {
    if (!isEdit) {
      resetCampos();
      campoId.value = "";
      formaSelect.value = "";
    }

    tipoInput.value = tipo;

    // mostra/esconde blocos
    grupoNormal.forEach((g) => {
      g.style.display = tipo === "normal" ? "flex" : "none";
    });
    grupoCartao.forEach((g) => {
      g.style.display = tipo === "cartao" ? "flex" : "none";
    });

    // ajusta título de acordo com novo vs edição e normal vs cartão
    if (tipo === "normal") {
      if (isEdit) {
        tituloModal.textContent = "Editar Despesa";
      } else {
        tituloModal.textContent = "Nova Despesa";
      }
      tituloModal.classList.remove("titulo-modal-despesa-cartao");
      tituloModal.classList.add("titulo-modal-despesa");

      faturaSelect.disabled = true;
      vencimentoInput.disabled = false;
    } else {
      if (isEdit) {
        tituloModal.textContent = "Editar Despesa Cartão";
      } else {
        tituloModal.textContent = "Nova Despesa Cartão";
      }
      tituloModal.classList.remove("titulo-modal-despesa");
      tituloModal.classList.add("titulo-modal-despesa-cartao");

      faturaSelect.disabled = false;
      vencimentoInput.disabled = true;
    }

    // nunca bloqueie o select de forma
    formaSelect.disabled = false;
  }

  // === Abrir modal (novo) - APENAS despesas de cartão ===
  // Botão específico do layout
  document.querySelectorAll(".btn-nova-despesa-cartao").forEach((btn) => {
    btn.addEventListener("click", () => {
      const tipo = btn.dataset.tipo || "cartao";

      toggleTipo(tipo, false);
      if (tipo === "cartao" && btn.dataset.idCartao) {
        cartaoSelect.value = btn.dataset.idCartao;
        cartaoSelect.dispatchEvent(new Event("change"));
      }
      modal.classList.add("exibir-modal");
    });
  });

  // Botão específico da página de cartão
  document.querySelectorAll("#btn-adicionar-despesa-cartao").forEach((btn) => {
    btn.addEventListener("click", () => {
      const tipo = btn.dataset.tipo || "cartao";

      toggleTipo(tipo, false);
      if (tipo === "cartao" && btn.dataset.idCartao) {
        cartaoSelect.value = btn.dataset.idCartao;
        cartaoSelect.dispatchEvent(new Event("change"));
      }
      modal.classList.add("exibir-modal");
    });
  });

  // === Fechar modal ===
  document.querySelectorAll(".btn-cancelar").forEach((btn) => {
    btn.addEventListener("click", () => {
      modal.classList.remove("exibir-modal");
    });
  });

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

  // === Popula faturas ao trocar cartão ===
  if (cartaoSelect) {
    cartaoSelect.addEventListener("change", () => {
      const opt = cartaoSelect.selectedOptions[0];
      contaCartaoInput.value = opt.dataset.conta;
      const fech = parseInt(opt.dataset.fechamento);
      const venc = parseInt(opt.dataset.vencimento);
      if (!venc) return;
      faturaSelect.innerHTML = '<option value="">Selecione</option>';
      const hoje = new Date();
      const base = new Date(hoje.getFullYear(), hoje.getMonth(), venc);
      for (let i = -2; i <= 4; i++) {
        const dv = new Date(base);
        dv.setMonth(dv.getMonth() + i);
        const df = new Date(dv.getFullYear(), dv.getMonth(), fech);
        let status =
          hoje >= df
            ? hoje.getMonth() === dv.getMonth() &&
              hoje.getFullYear() === dv.getFullYear()
              ? "Fechada"
              : dv > hoje
              ? "Parcial"
              : "Zerada"
            : "Aberta";
        const val = dv.toISOString().split("T")[0];
        const lbl = dv.toLocaleDateString("pt-BR", {
          day: "2-digit",
          month: "long",
          year: "numeric",
        });
        const o = document.createElement("option");
        o.value = val;
        o.textContent = `${lbl} (${status})`;
        faturaSelect.appendChild(o);
      }
      const primeira = Array.from(faturaSelect.options).find((o) =>
        o.text.endsWith("(Aberta)")
      );
      if (primeira) primeira.selected = true;
    });
  }

  // === Botões de editar - usando event delegation para ter prioridade ===
  document.addEventListener(
    "click",
    (e) => {
      if (e.target.closest(".btn-editar-despesa")) {
        const btn = e.target.closest(".btn-editar-despesa");
        const isCartao = btn.dataset.isCartao === "1";

        // Só processa se for despesa de cartão
        if (!isCartao) {
          return;
        }

        // Se chegou aqui, é despesa de cartão - processar
        e.preventDefault();
        e.stopPropagation();

        const tipo = "cartao";
        toggleTipo(tipo, true);
        modal.classList.add("exibir-modal");

        // Aguardar um pouco para o modal estar totalmente renderizado
        setTimeout(() => {
          // campos comuns
          campoId.value = btn.dataset.id || btn.getAttribute("data-id");

          // Campo descrição - busca específica no modal de cartão
          const descricaoInput = form.querySelector('input[name="descricao"]');
          if (descricaoInput) {
            descricaoInput.value = btn.dataset.descricao || "";
          } else {
            console.error(
              "Campo de descrição não encontrado no modal de cartão"
            );
          }
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

          // ** NOVO: data de vencimento **
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

          // ** NOVO: observações **
          form.querySelector('[name="observacoes"]').value =
            btn.dataset.observacoes || "";

          // conta / fatura ou conta normal
          if (isCartao) {
            cartaoSelect.value = btn.dataset.cartao;
            cartaoSelect.dispatchEvent(new Event("change"));
            faturaSelect.value = btn.dataset.vencimento;
          } else {
            document.getElementById("conta-select-cartao").value =
              btn.dataset.contaId;
          }

          // ** NOVO: forma de transação **
          formaSelect.value = btn.dataset.formaId;
        }, 100); // Fim do setTimeout
      }
    },
    true
  ); // true = capture phase para ter prioridade

  // === SUBMIT ===
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

    // 4) Forma de transação
    // — se for cartão, força forma = 2; senão, pega do select
    if (tipoInput.value === "cartao") {
      formData.set("forma", "2");
    } else {
      const formaSelect = document.getElementById(
        "forma-transacao-select-cartao"
      );
      formData.set("forma", formaSelect.value);
    }

    // 5) Conta e vencimento/fatura
    if (tipoInput.value === "cartao") {
      formData.set("id_conta", contaCartaoInput.value);
      formData.set("data_vencimento", faturaSelect.value);
    } else {
      formData.set(
        "id_conta",
        document.getElementById("conta-select-cartao").value
      );
      formData.set("data_vencimento", vencimentoInput.value);
    }

    // 6) Flags adicionais
    formData.set("parcelado", checkboxParcelado.checked ? "1" : "0");
    formData.set(
      "recorrente",
      document.getElementById("check-recorrente").checked ? "1" : "0"
    );
    const esc = form.querySelector("input[name='escopo_edicao']:checked");
    if (esc) formData.set("modo_edicao", esc.value);

    // 7) envia
    const isEdicao = campoId.value !== "";
    let url;
    if (tipoInput.value === "cartao") {
      url = isEdicao
        ? "/despesas-cartao/atualizar-fatura"
        : "/despesas-cartao/salvar-fatura";
    } else {
      url = isEdicao ? "/despesas/atualizar" : "/despesas/salvar";
    }
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
