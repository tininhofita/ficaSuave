// ===== PAGAMENTO DE DESPESAS NORMAIS =====

// Variável global para armazenar dados da despesa a ser excluída
let despesaParaExcluir = null;

document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("modalPagarDespesa");
  const form = document.getElementById("formPagarDespesa");
  const btnCancelar = document.getElementById("cancelarPagamentoDespesa");
  const btnFechar = document.getElementById("fecharModalPagamento");
  const saldoWarning = document.getElementById("saldoWarningDespesa");

  // Elementos do modal de exclusão
  const modalExclusao = document.getElementById("modalExcluirDespesa");
  const btnCancelarExclusao = document.getElementById("cancelarExclusao");
  const btnFecharExclusao = document.getElementById("fecharModalExclusao");
  const btnConfirmarExclusao = document.getElementById("confirmarExclusao");

  // Campos do formulário
  const idDespesaInput = document.getElementById("id_despesa_pagamento");
  const nomeDespesaSpan = document.getElementById("nome_despesa_pagamento");
  const valorOriginalSpan = document.getElementById("valor_original_pagamento");
  const valorPagamentoInput = document.getElementById(
    "valor_pagamento_despesa"
  );
  const contaSelect = document.getElementById("conta_pagamento_despesa");
  const dataPagamentoInput = document.getElementById("data_pagamento_despesa");

  // Event listeners para abrir modal - usando capture para ter prioridade
  document.addEventListener(
    "click",
    function (e) {
      if (e.target.closest(".btn-pagar-despesa")) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        const btn = e.target.closest(".btn-pagar-despesa");
        abrirModalPagamento(btn);
        return false;
      }

      if (e.target.closest(".btn-excluir-despesa")) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        const btn = e.target.closest(".btn-excluir-despesa");
        abrirModalExclusao(btn);
        return false;
      }
    },
    true
  ); // true = capture phase

  // Event listeners para fechar modal
  if (btnCancelar) {
    btnCancelar.addEventListener("click", fecharModal);
  }
  if (btnFechar) {
    btnFechar.addEventListener("click", fecharModal);
  }

  // Event listeners para modal de exclusão - só se os elementos existirem
  if (btnCancelarExclusao) {
    btnCancelarExclusao.addEventListener("click", fecharModalExclusao);
  }
  if (btnFecharExclusao) {
    btnFecharExclusao.addEventListener("click", fecharModalExclusao);
  }
  if (btnConfirmarExclusao) {
    btnConfirmarExclusao.addEventListener("click", confirmarExclusao);
  }

  // Fechar modal clicando fora
  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === modal) {
        fecharModal();
      }
    });
  }

  // Verificar saldo quando seleciona conta ou muda valor
  contaSelect.addEventListener("change", verificarSaldo);
  valorPagamentoInput.addEventListener("input", verificarSaldo);

  // === Máscara de Valor (formatação brasileira) ===
  valorPagamentoInput.addEventListener("input", function (e) {
    let valor = e.target.value.replace(/\D/g, "");

    if (valor === "") {
      e.target.value = "";
      return;
    }

    // Converte para número e formata como moeda brasileira
    const valorNumerico = parseFloat(valor) / 100;
    const valorFormatado = valorNumerico.toLocaleString("pt-BR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

    e.target.value = valorFormatado;
  });

  // Submissão do formulário
  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const btn = form.querySelector('button[type="submit"]');
    const btnText = btn.querySelector(".btn-text");

    btn.disabled = true;
    btnText.textContent = "Processando...";

    try {
      const formData = new FormData(form);

      // Converter valor para formato numérico
      const valorStr = formData.get("valor").replace(",", ".");
      formData.set("valor", valorStr);

      const response = await fetch("/despesas/pagar", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.sucesso) {
        alert("✅ Pagamento confirmado com sucesso!");
        location.reload();
      } else {
        throw new Error(data.erro || "Erro ao processar pagamento");
      }
    } catch (error) {
      alert("❌ Erro ao confirmar pagamento: " + error.message);
      btn.disabled = false;
      btnText.textContent = "Confirmar Pagamento";
    }
  });

  function abrirModalPagamento(btn) {
    // Preencher dados da despesa
    const idDespesa = btn.dataset.id;
    const nomeDespesa = btn.dataset.nome;
    const valorOriginal = btn.dataset.valor;

    idDespesaInput.value = idDespesa;
    nomeDespesaSpan.textContent = nomeDespesa;
    valorOriginalSpan.textContent = valorOriginal;

    // Definir valor padrão (remover formatação e aplicar máscara)
    const valorLimpo = valorOriginal.replace(/[^\d,]/g, "").replace(",", ".");
    const valorNumerico = parseFloat(valorLimpo);
    valorPagamentoInput.value = valorNumerico.toLocaleString("pt-BR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

    // Resetar seleções
    contaSelect.value = "";
    saldoWarning.style.display = "none";

    // Mostrar modal centralizado
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
  }

  function fecharModal() {
    modal.style.display = "none";
    document.body.style.overflow = "auto";

    // Resetar formulário
    form.reset();
    saldoWarning.style.display = "none";
  }

  function verificarSaldo() {
    const valorPagamento =
      parseFloat(valorPagamentoInput.value.replace(",", ".")) || 0;
    const option = contaSelect.options[contaSelect.selectedIndex];

    if (option.value && valorPagamento > 0) {
      const saldoConta = parseFloat(option.dataset.saldo) || 0;
      const saldoRestante = saldoConta - valorPagamento;

      if (saldoRestante < 0) {
        saldoWarning.style.display = "block";
      } else {
        saldoWarning.style.display = "none";
      }
    } else {
      saldoWarning.style.display = "none";
    }
  }

  // ===== FUNÇÕES DO MODAL DE EXCLUSÃO =====
  function abrirModalExclusao(btn) {
    if (!btn) return;

    despesaParaExcluir = {
      id: btn.dataset.id,
      parcelado: btn.dataset.parcelado === "1",
      numeroParcelas: btn.dataset.numeroParcelas,
      totalParcelas: btn.dataset.totalParcelas,
    };

    // Buscar dados da despesa na tabela
    const linha = btn.closest("tr");
    const descricao = linha.querySelector("td:nth-child(2)").textContent.trim(); // Ajustado para coluna 2
    const valor = linha.querySelector("td:nth-child(4)").textContent.trim(); // Ajustado para coluna 4

    // Preencher modal - só se os elementos existirem
    const nomeElement = document.getElementById("nome_despesa_exclusao");
    const valorElement = document.getElementById("valor_despesa_exclusao");

    if (nomeElement) {
      nomeElement.textContent = descricao;
    }
    if (valorElement) {
      valorElement.textContent = valor;
    }

    // Definir escopo padrão
    const escopoSelect = document.getElementById("escopo_exclusao");
    if (despesaParaExcluir.parcelado) {
      escopoSelect.value = "somente";
    } else {
      escopoSelect.value = "somente";
    }

    // Mostrar modal - só se existir
    if (modalExclusao) {
      modalExclusao.style.display = "flex";
      document.body.style.overflow = "hidden";
    }
  }

  function fecharModalExclusao() {
    if (modalExclusao) {
      modalExclusao.style.display = "none";
      document.body.style.overflow = "auto";
    }
    despesaParaExcluir = null;
  }

  async function confirmarExclusao() {
    if (!despesaParaExcluir) return;

    const escopoElement = document.getElementById("escopo_exclusao");
    const escopo = escopoElement ? escopoElement.value : "somente";
    const btnText = btnConfirmarExclusao
      ? btnConfirmarExclusao.querySelector(".btn-text")
      : null;

    // Desabilitar botão e mostrar loading
    if (btnConfirmarExclusao) {
      btnConfirmarExclusao.disabled = true;
    }
    if (btnText) {
      btnText.textContent = "Excluindo...";
    }

    try {
      const response = await fetch("/despesas/excluir", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          id: despesaParaExcluir.id,
          escopo: escopo,
        }),
      });

      const data = await response.json();

      if (data.sucesso) {
        // Recarregar a página para atualizar a lista
        window.location.reload();
      } else {
        alert("Erro ao excluir despesa: " + (data.erro || "Erro desconhecido"));
      }
    } catch (error) {
      console.error("Erro:", error);
      alert("Erro ao excluir despesa. Tente novamente.");
    } finally {
      // Reabilitar botão
      if (btnConfirmarExclusao) {
        btnConfirmarExclusao.disabled = false;
      }
      if (btnText) {
        btnText.textContent = "Confirmar Exclusão";
      }
    }
  }

  // Fechar modal de exclusão clicando fora
  if (modalExclusao) {
    modalExclusao.addEventListener("click", function (e) {
      if (e.target === modalExclusao) {
        fecharModalExclusao();
      }
    });
  }
});
