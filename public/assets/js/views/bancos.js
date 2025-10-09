document.addEventListener("DOMContentLoaded", () => {
  // ========= ELEMENTOS =========
  const modal = document.getElementById("modal-cadastro-banco");
  const form = document.getElementById("form-cadastro-banco");
  const btnNovo = document.getElementById("btn-adicionar-banco");
  const btnCancel = document.querySelector(".btn-cancelar");
  const inputId = document.getElementById("id_conta");
  const inputNome = document.getElementById("nome_conta");
  const selectTipo = document.getElementById("tipo");
  const inputBanco = document.getElementById("banco");
  const checkAtiva = document.getElementById("ativa");
  const h3Titulo = document.getElementById("titulo-modal-banco");
  const saldoInput = document.getElementById("saldo_inicial");

  // ========= ELEMENTOS TRANSFERÊNCIA =========
  const modalTransferencia = document.getElementById("modal-transferencia");
  const formTransferencia = document.getElementById("form-transferencia");
  const btnTransferencia = document.getElementById("btn-transferencia");
  const btnCancelTransferencia = document.querySelector(
    ".btn-cancelar-transferencia"
  );
  const selectContaOrigem = document.getElementById("conta-origem");
  const selectContaDestino = document.getElementById("conta-destino");
  const valorTransferencia = document.getElementById("valor-transferencia");
  const saldoOrigem = document.getElementById("saldo-origem");
  const saldoDestino = document.getElementById("saldo-destino");
  const resumoValor = document.getElementById("resumo-valor");
  const resumoTaxa = document.getElementById("resumo-taxa");
  const resumoTotal = document.getElementById("resumo-total");

  // ========= DADOS DAS CONTAS =========
  const contasData = [];
  document.querySelectorAll(".tabela-bancos tbody tr").forEach((row) => {
    const data = {
      id: row.dataset.id,
      nome: row.cells[1].querySelector("strong").textContent,
      saldo: parseFloat(
        row.cells[5]
          .querySelector(".saldo-atual")
          .textContent.replace(/[^\d,]/g, "")
          .replace(",", ".")
      ),
    };
    contasData.push(data);
  });

  // ========= FUNÇÕES AUX =========
  function abrirModal() {
    modal.classList.add("exibir-modal");
  }
  function fecharModal() {
    modal.classList.remove("exibir-modal");
  }
  function abrirModalTransferencia() {
    carregarContasTransferencia();
    modalTransferencia.classList.add("exibir-modal");
  }
  function fecharModalTransferencia() {
    modalTransferencia.classList.remove("exibir-modal");
    resetFormTransferencia();
  }
  function resetFormNovo() {
    form.reset();
    inputId.value = "";
    h3Titulo.textContent = "Cadastro de Conta Bancária";
    // zera máscara do saldo
    if (saldoInput) {
      saldoInput.value = "0,00";
      saldoInput.setAttribute("data-raw", "0.00");
    }
  }
  function resetFormTransferencia() {
    formTransferencia.reset();
    selectContaOrigem.value = "";
    selectContaDestino.value = "";
    valorTransferencia.value = "";
    saldoOrigem.textContent = "R$ 0,00";
    saldoDestino.textContent = "R$ 0,00";
    resumoValor.textContent = "R$ 0,00";
    resumoTaxa.textContent = "R$ 0,00";
    resumoTotal.textContent = "R$ 0,00";
  }
  function carregarContasTransferencia() {
    // Limpar selects
    selectContaOrigem.innerHTML = '<option value="">Selecione a conta</option>';
    selectContaDestino.innerHTML =
      '<option value="">Selecione a conta</option>';

    // Carregar TODAS as contas (não apenas as com saldo > 0)
    contasData.forEach((conta) => {
      const saldoFormatado = conta.saldo.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
      });

      // Para origem: mostrar todas as contas, mas destacar saldo
      const optionOrigem = new Option(
        `${conta.nome} (R$ ${saldoFormatado})`,
        conta.id
      );

      // Para destino: mostrar todas as contas
      const optionDestino = new Option(conta.nome, conta.id);

      selectContaOrigem.add(optionOrigem);
      selectContaDestino.add(optionDestino);
    });
  }
  function preencherFormEdicao(btn) {
    inputId.value = btn.dataset.id || "";
    inputNome.value = btn.dataset.nome || "";
    selectTipo.value = btn.dataset.tipo || "corrente";
    inputBanco.value = btn.dataset.banco || "";
    checkAtiva.checked = btn.dataset.ativa === "1";

    // saldo com máscara (se veio no dataset)
    if (saldoInput) {
      const valorMascarado = btn.dataset.saldoInicial || "0,00";
      saldoInput.value = valorMascarado;
      const raw = (valorMascarado || "0,00")
        .replace(/\./g, "")
        .replace(",", ".");
      const num = parseFloat(raw) || 0;
      saldoInput.setAttribute("data-raw", num.toFixed(2));
    }
    h3Titulo.textContent = "Editar Conta Bancária";
  }

  // ========= MÁSCARA BRL =========
  if (saldoInput) {
    saldoInput.addEventListener("input", (e) => {
      let v = e.target.value.replace(/\D/g, "");
      if (!v) v = "0";
      v = (parseInt(v, 10) / 100).toFixed(2); // 123456 -> "1234.56"
      const [int, dec] = v.split(".");
      const intFmt = int.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      e.target.value = `${intFmt},${dec}`; // "1.234,56"
      e.target.setAttribute("data-raw", v); // guarda "1234.56"
    });
    saldoInput.addEventListener("focus", () => {
      if (!saldoInput.value.trim()) {
        saldoInput.value = "0,00";
        saldoInput.setAttribute("data-raw", "0.00");
      }
    });
  }

  // ========= FUNÇÕES DE TRANSFERÊNCIA =========
  function atualizarSaldoOrigem() {
    const contaId = selectContaOrigem.value;
    const conta = contasData.find((c) => c.id === contaId);
    if (conta) {
      saldoOrigem.textContent = `R$ ${conta.saldo.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
      })}`;
    } else {
      saldoOrigem.textContent = "R$ 0,00";
    }
    atualizarResumoTransferencia();
  }

  function atualizarSaldoDestino() {
    const contaId = selectContaDestino.value;
    const conta = contasData.find((c) => c.id === contaId);
    if (conta) {
      saldoDestino.textContent = `R$ ${conta.saldo.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
      })}`;
    } else {
      saldoDestino.textContent = "R$ 0,00";
    }
  }

  function atualizarResumoTransferencia() {
    // Pegar o valor do input após a máscara
    let valor = 0;

    if (valorTransferencia.value) {
      // A máscara já converte para formato: "10,00"
      // Precisamos apenas converter para número
      const valorLimpo = valorTransferencia.value.replace(/[^\d,]/g, "");
      if (valorLimpo.includes(",")) {
        // Formato brasileiro: "10,00" -> 10.00
        valor = parseFloat(valorLimpo.replace(",", ".")) || 0;
      }
    }

    const taxa = 0; // Por enquanto sem taxa
    const total = valor + taxa;

    resumoValor.textContent = `R$ ${valor.toLocaleString("pt-BR", {
      minimumFractionDigits: 2,
    })}`;
    resumoTaxa.textContent = `R$ ${taxa.toLocaleString("pt-BR", {
      minimumFractionDigits: 2,
    })}`;
    resumoTotal.textContent = `R$ ${total.toLocaleString("pt-BR", {
      minimumFractionDigits: 2,
    })}`;
  }

  // ========= AÇÕES =========
  btnNovo?.addEventListener("click", () => {
    resetFormNovo();
    abrirModal();
  });

  btnTransferencia?.addEventListener("click", () => {
    abrirModalTransferencia();
  });

  btnCancel?.addEventListener("click", () => fecharModal());
  btnCancelTransferencia?.addEventListener("click", () =>
    fecharModalTransferencia()
  );

  modal?.addEventListener("click", (e) => {
    if (!e.target.closest(".modal-content")) fecharModal();
  });

  modalTransferencia?.addEventListener("click", (e) => {
    if (!e.target.closest(".modal-content")) fecharModalTransferencia();
  });

  // Submit (novo/editar) — sempre normaliza saldo_inicial
  form?.addEventListener("submit", (e) => {
    e.preventDefault();

    // normaliza saldo_inicial
    let raw = saldoInput?.getAttribute("data-raw");
    if (!raw) {
      const txt = (saldoInput?.value || "0")
        .replace(/\./g, "")
        .replace(",", ".");
      const num = parseFloat(txt) || 0;
      raw = num.toFixed(2);
      saldoInput?.setAttribute("data-raw", raw);
    }

    const fd = new FormData(form);
    fd.set("saldo_inicial", raw); // garante número com ponto

    const id = fd.get("id_conta");
    const rota = id ? "/bancos/editar" : "/bancos/salvar";

    fetch(rota, { method: "POST", body: fd })
      .then((res) => {
        if (res.redirected) window.location.href = res.url;
      })
      .catch((err) => {
        console.error(err);
        alert("Erro ao salvar conta.");
      });
  });

  // Favoritar (apenas uma por usuário)
  document.querySelectorAll(".btn-favorita").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const id = btn.dataset.id;
      try {
        const res = await fetch("/bancos/favoritar", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id=${encodeURIComponent(id)}`,
        });

        let data;
        try {
          data = await res.json();
        } catch {
          const txt = await res.text();
          console.error("Resposta não-JSON:", txt);
          alert("Falha ao definir favorita (resposta inesperada).");
          return;
        }

        if (!data?.sucesso) {
          alert(data?.erro || "Falha ao definir/desfazer favorita");
          return;
        }

        window.location.reload();
      } catch (e) {
        console.error(e);
        alert("Erro de rede ao definir favorita.");
      }
    });
  });

  // Transferência - Event Listeners
  selectContaOrigem?.addEventListener("change", atualizarSaldoOrigem);
  selectContaDestino?.addEventListener("change", atualizarSaldoDestino);
  valorTransferencia?.addEventListener("input", atualizarResumoTransferencia);

  // Máscara de valor para transferência
  if (valorTransferencia) {
    valorTransferencia.addEventListener("input", (e) => {
      let v = e.target.value.replace(/\D/g, "");
      if (!v) v = "0";
      v = (parseInt(v, 10) / 100).toFixed(2);
      const [int, dec] = v.split(".");
      const intFmt = int.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      e.target.value = `${intFmt},${dec}`;
      // Atualizar resumo após aplicar máscara
      atualizarResumoTransferencia();
    });
  }

  // Submit Transferência
  formTransferencia?.addEventListener("submit", (e) => {
    e.preventDefault();

    const contaOrigem = selectContaOrigem.value;
    const contaDestino = selectContaDestino.value;

    // Processar valor da mesma forma que a função de resumo
    let valor = 0;
    if (valorTransferencia.value) {
      const valorLimpo = valorTransferencia.value.replace(/[^\d,]/g, "");
      if (valorLimpo.includes(",")) {
        valor = parseFloat(valorLimpo.replace(",", ".")) || 0;
      }
    }

    if (!contaOrigem || !contaDestino) {
      alert("Selecione as contas de origem e destino");
      return;
    }

    if (contaOrigem === contaDestino) {
      alert("As contas de origem e destino devem ser diferentes");
      return;
    }

    if (!valor || valor <= 0) {
      alert("Digite um valor válido para transferência");
      return;
    }

    const contaOrigemData = contasData.find((c) => c.id === contaOrigem);
    if (contaOrigemData && valor > contaOrigemData.saldo) {
      alert("Saldo insuficiente na conta de origem");
      return;
    }

    if (
      !confirm(
        `Confirmar transferência de R$ ${valor.toLocaleString("pt-BR", {
          minimumFractionDigits: 2,
        })}?`
      )
    ) {
      return;
    }

    // Enviar dados
    const formData = new FormData(formTransferencia);
    formData.append("valor_transferencia", valor.toString());

    fetch("/bancos/transferir", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (response.redirected) {
          window.location.href = response.url;
        } else {
          return response.json();
        }
      })
      .then((data) => {
        if (data && data.sucesso) {
          alert(data.mensagem || "Transferência realizada com sucesso!");
          fecharModalTransferencia();
          window.location.reload();
        } else if (data && !data.sucesso) {
          alert(data.erro || "Erro ao realizar transferência");
        }
      })
      .catch((error) => {
        console.error("Erro:", error);
        alert("Erro ao realizar transferência");
      });
  });

  // Editar
  document.querySelectorAll(".btn-editar").forEach((btn) => {
    btn.addEventListener("click", () => {
      resetFormNovo(); // garante estado limpo
      preencherFormEdicao(btn); // carrega dados do dataset
      abrirModal();
    });
  });

  // Transferir para conta específica
  document.querySelectorAll(".btn-transferir").forEach((btn) => {
    btn.addEventListener("click", () => {
      const contaDestinoId = btn.dataset.id;
      const contaDestinoNome = btn.dataset.nome;

      // Carregar contas disponíveis (excluindo a conta de destino)
      selectContaOrigem.innerHTML =
        '<option value="">Selecione a conta de origem</option>';
      selectContaDestino.innerHTML = `<option value="${contaDestinoId}" selected>${contaDestinoNome}</option>`;

      contasData.forEach((conta) => {
        if (conta.id !== contaDestinoId) {
          const saldoFormatado = conta.saldo.toLocaleString("pt-BR", {
            minimumFractionDigits: 2,
          });
          const optionOrigem = new Option(
            `${conta.nome} (R$ ${saldoFormatado})`,
            conta.id
          );
          selectContaOrigem.add(optionOrigem);
        }
      });

      atualizarSaldoDestino();
      abrirModalTransferencia();
    });
  });

  // Excluir
  document.querySelectorAll(".btn-excluir").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (!confirm("Tem certeza que deseja excluir esta conta bancária?"))
        return;
      fetch("/bancos/excluir", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${encodeURIComponent(btn.dataset.id)}`,
      })
        .then((res) => {
          if (res.redirected) window.location.href = res.url;
        })
        .catch((err) => {
          console.error(err);
          alert("Erro ao excluir conta.");
        });
    });
  });
});
