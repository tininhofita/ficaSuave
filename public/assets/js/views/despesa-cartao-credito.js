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

  document.querySelectorAll(".btn-pagar-fatura").forEach((botao) => {
    botao.addEventListener("click", () => {
      const idCartao = botao.dataset.idCartao;
      const valorFatura = parseFloat(botao.dataset.valorFatura) || 0;

      idCartaoInput.value = idCartao;
      valorFaturaSpan.textContent = valorFatura.toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL",
      });

      modal.classList.add("show");
    });
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

      if (!res.ok) throw new Error();
      alert("Fatura paga com sucesso!");
      location.reload();
    } catch {
      alert("Erro ao pagar fatura.");
      btn.disabled = false;
      btn.textContent = "Confirmar Pagamento";
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
  document.querySelectorAll(".btn-excluir-despesa").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const id = btn.dataset.id;
      const desc = btn.dataset.descricao || "";
      const valor = btn.dataset.valor || "";
      const ehParcelado =
        btn.dataset.parcelado === "1" || btn.dataset.parcelado === "true";

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
        const res = await fetch("/despesas/excluir", {
          method: "POST",
          body: fd,
        });

        // tenta ler JSON, mas aceita sucesso só pelo status também
        let sucesso = false;
        const ct = res.headers.get("content-type") || "";
        if (ct.includes("application/json")) {
          const json = await res.json();
          sucesso = !!json.sucesso;
        } else {
          sucesso = res.ok;
        }

        if (sucesso) {
          alert("Despesa excluída com sucesso!");
          location.reload();
        } else {
          alert("Falha ao excluir. Tente novamente.");
        }
      } catch (e) {
        alert("Erro de rede ao excluir.");
      }
    });
  });

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
