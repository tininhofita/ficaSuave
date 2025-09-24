document.addEventListener("DOMContentLoaded", function () {
  // -------- Navegação de Mês --------
  const mesSpan = document.querySelector(".filtro-mes span");
  const btnAnterior = document.querySelector(".filtro-mes button:first-child");
  const btnProximo = document.querySelector(".filtro-mes button:last-child");

  const urlParams = new URLSearchParams(window.location.search);
  let mes = parseInt(urlParams.get("mes")) || new Date().getMonth() + 1;
  let ano = parseInt(urlParams.get("ano")) || new Date().getFullYear();
  let dataAtual = new Date(ano, mes - 1);

  function atualizarTextoMes() {
    const options = { month: "long", year: "numeric" };
    mesSpan.textContent = dataAtual.toLocaleDateString("pt-BR", options);
  }

  function carregarFaturaDoMes() {
    const novoMes = dataAtual.getMonth() + 1;
    const novoAno = dataAtual.getFullYear();

    const url = new URL(window.location.href);
    url.searchParams.set("mes", novoMes);
    url.searchParams.set("ano", novoAno);
    window.location.href = url.toString();
  }

  if (btnAnterior && btnProximo) {
    btnAnterior.addEventListener("click", () => {
      dataAtual.setMonth(dataAtual.getMonth() - 1);
      carregarFaturaDoMes();
    });

    btnProximo.addEventListener("click", () => {
      dataAtual.setMonth(dataAtual.getMonth() + 1);
      carregarFaturaDoMes();
    });
  }

  atualizarTextoMes();

  // -------- Menu Toggle --------
  const toggle = document.getElementById("menu-toggle");
  const menu = document.getElementById("menu-opcoes");

  if (toggle && menu) {
    toggle.addEventListener("click", function (e) {
      e.stopPropagation();
      menu.classList.toggle("show");
    });

    document.addEventListener("click", function (e) {
      if (!menu.contains(e.target)) {
        menu.classList.remove("show");
      }
    });
  }

  // -------- Busca de Fatura --------
  const wrapper = document.getElementById("search-wrapper");
  const input = document.getElementById("busca-fatura");
  const btn = document.getElementById("btn-buscar");
  const linhas = document.querySelectorAll(".tabela-despesas tbody tr");

  if (btn && input && wrapper) {
    btn.addEventListener("click", (e) => {
      if (!wrapper.classList.contains("expandido")) {
        e.preventDefault();
        wrapper.classList.add("expandido");
        input.focus();
      }
    });

    input.addEventListener("input", () => {
      const termo = input.value.toLowerCase();
      linhas.forEach((tr) => {
        const texto = tr.innerText.toLowerCase();
        tr.style.display = texto.includes(termo) ? "" : "none";
      });
    });

    document.addEventListener("click", (e) => {
      if (!wrapper.contains(e.target)) {
        wrapper.classList.remove("expandido");
        input.value = "";
        linhas.forEach((tr) => (tr.style.display = ""));
      }
    });
  }

  // -------- Modal Pagar Fatura --------
  const modal = document.getElementById("modalPagarFatura");
  const idCartaoInput = document.getElementById("id_cartao");
  const valorFaturaSpan = document.getElementById("valor_fatura");
  const form = document.getElementById("formPagarFatura");
  const btnFechar = document.getElementById("fecharModal");
  const saldoWarning = document.getElementById("saldoWarning");
  const contaSelect = document.getElementById("id_conta");
  let valorFaturaAtual = 0;

  document.querySelectorAll(".btn-pagar-fatura").forEach((botao) => {
    botao.addEventListener("click", () => {
      const idCartao = botao.dataset.idCartao;
      const valorFatura = parseFloat(botao.dataset.valorFatura) || 0;

      valorFaturaAtual = valorFatura;
      idCartaoInput.value = idCartao;
      valorFaturaSpan.textContent = valorFatura.toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL",
      });

      // Esconde aviso de saldo ao abrir modal
      saldoWarning.style.display = "none";

      modal.classList.add("show");
    });
  });

  // Verifica saldo quando seleciona conta
  contaSelect.addEventListener("change", () => {
    const option = contaSelect.options[contaSelect.selectedIndex];
    if (option.value) {
      const saldoConta = parseFloat(option.dataset.saldo) || 0;
      const saldoRestante = saldoConta - valorFaturaAtual;

      if (saldoRestante < 0) {
        saldoWarning.style.display = "block";
      } else {
        saldoWarning.style.display = "none";
      }
    } else {
      saldoWarning.style.display = "none";
    }
  });

  btnFechar.addEventListener("click", () => {
    modal.classList.remove("show");
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.remove("show");
    }
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const btn = form.querySelector("button[type=submit]");
    btn.disabled = true;
    btn.textContent = "Processando...";

    const formData = new FormData(form);
    formData.append("mes", mes);
    formData.append("ano", ano);

    try {
      const res = await fetch("/despesas-cartao/pagarFatura", {
        method: "POST",
        body: formData,
      });

      const data = await res.json();

      if (!res.ok || !data.sucesso) {
        throw new Error(data.erro || "Erro ao processar pagamento");
      }

      // Mensagem de sucesso com informações sobre saldo
      let mensagem = "✅ Fatura paga com sucesso!";

      if (data.saldo_negativo) {
        mensagem += `\n\n⚠️ Atenção: A conta ficou com saldo negativo de R$ ${Math.abs(
          data.saldo_atual
        ).toLocaleString("pt-BR", {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        })}.`;
      }

      alert(mensagem);
      location.reload();
    } catch (error) {
      alert("❌ Erro ao pagar fatura: " + error.message);
      btn.disabled = false;
      btn.querySelector(".btn-text").textContent = "Confirmar Pagamento";
    }
  });

  document.getElementById("cancelarPagamento").addEventListener("click", () => {
    modal.classList.remove("show");
  });

  // abre modal de estorno
  document.getElementById("menu-estorno").addEventListener("click", (e) => {
    e.preventDefault();
    const link = e.currentTarget; // pega o <a>
    const idCartao = link.dataset.idCartao; // lê o data-id-cartao
    document.getElementById("estorno_id_cartao").value = idCartao;
    document.getElementById("modalEstorno").classList.add("show");
  });

  // fecha modal
  document.querySelectorAll("[data-close]").forEach((btn) => {
    btn.addEventListener("click", () => {
      document.querySelector(btn.dataset.close).classList.remove("show");
    });
  });

  // envia estorno
  document
    .getElementById("formEstorno")
    .addEventListener("submit", async (e) => {
      e.preventDefault();
      const form = e.target;
      const btn = form.querySelector("button[type=submit]");
      btn.disabled = true;
      btn.textContent = "Processando...";

      // monta FormData
      const data = new FormData(form);
      data.append("mes", mes);
      data.append("ano", ano);

      // **AQUI** você coloca a checagem de endpoint e resposta:
      const endpoint = "/despesas-cartao/estornarFatura"; // comece com barra
      const res = await fetch(endpoint, {
        method: "POST",
        body: data,
      });

      // verifica se deu 404, 500, etc, antes de parsear JSON
      if (!res.ok) {
        alert("Erro no servidor: " + res.status);
        btn.disabled = false;
        btn.textContent = "Confirmar Estorno";
        return;
      }

      // agora sim parseia o JSON
      const json = await res.json();
      if (!json.sucesso) {
        alert("Erro no estorno: " + (json.mensagem || ""));
        btn.disabled = false;
        btn.textContent = "Confirmar Estorno";
        return;
      }

      // se chegou aqui, foi sucesso
      alert("Fatura estornada com sucesso!");
      form.closest(".modal-custom").classList.remove("show");
      location.reload();
    });

  // -------- Excluir despesa (fatura) --------
  document.addEventListener(
    "click",
    async (e) => {
      if (e.target.closest(".btn-excluir-despesa")) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        const btn = e.target.closest(".btn-excluir-despesa");
        console.log("Botão de excluir clicado!");

        const id = btn.dataset.id;
        const desc = btn.dataset.descricao || "";
        const valor = btn.dataset.valor || "";
        const ehParcelado =
          btn.dataset.parcelado === "1" || btn.dataset.parcelado === "true";

        console.log("Dados:", { id, desc, valor, ehParcelado });

        if (!id) {
          alert("Erro: ID da despesa não encontrado!");
          return;
        }

        // Confirmação + escolha de escopo (se parcelado)
        let escopo = "somente";
        if (ehParcelado) {
          const escolha = prompt(
            `Excluir "${desc}" (${valor}).\n\nDigite uma opção:\n- somente\n- futuras\n- todas`,
            "somente"
          );
          if (!escolha) return;
          const op = escolha.toLowerCase().trim();
          if (!["somente", "futuras", "todas"].includes(op)) {
            alert("Opção inválida. Use: somente, futuras ou todas.");
            return;
          }
          escopo = op;
        } else {
          const ok = confirm(`Excluir "${desc}" (${valor})?`);
          if (!ok) return;
        }

        const fd = new FormData();
        fd.append("id", id);
        fd.append("escopo", escopo);

        try {
          const res = await fetch("/despesas-cartao/excluir-fatura", {
            method: "POST",
            body: fd,
          });

          // Sempre tenta ler como texto primeiro
          const responseText = await res.text();

          let sucesso = false;
          try {
            const json = JSON.parse(responseText);
            sucesso = !!json.sucesso;
          } catch (parseError) {
            sucesso = res.ok;
          }

          if (sucesso) {
            alert("Despesa excluída com sucesso!");
            location.reload();
          } else {
            alert("Falha ao excluir. Tente novamente.");
          }
        } catch (e) {
          console.error("Erro ao excluir:", e);
          alert("Erro de rede ao excluir: " + e.message);
        }
      }
    },
    true
  ); // true = capture phase para ter prioridade

  // -------- Seleção múltipla com checkbox --------
  const tbody = document.querySelector(".tabela-despesas tbody");
  if (tbody) {
    // marca/desmarca e aplica destaque na linha
    tbody.addEventListener("change", (e) => {
      if (!e.target.classList.contains("row-select")) return;
      const tr = e.target.closest("tr");
      tr.classList.toggle("selected", e.target.checked);
    });

    // clicar na linha (fora de botões/links/inputs) alterna o checkbox
    tbody.addEventListener("click", (e) => {
      if (e.target.closest("button, a, input, select, label, i")) return;
      const tr = e.target.closest("tr");
      if (!tr) return;
      const cb = tr.querySelector(".row-select");
      if (!cb) return;
      cb.checked = !cb.checked;
      cb.dispatchEvent(new Event("change", { bubbles: true }));
    });
  }
});
