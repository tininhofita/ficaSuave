document.addEventListener("DOMContentLoaded", () => {
  const filtroStatus = document.getElementById("filtro-status");
  const filtroAno = document.getElementById("filtro-ano");
  const filtroMes = document.getElementById("filtro-mes");
  const filtroConta = document.getElementById("filtro-conta");
  const filtroForma = document.getElementById("filtro-forma");
  const filtroCategoria = document.getElementById("filtro-categoria");
  const inputBusca = document.getElementById("filtro-busca");
  const selectOrdem = document.getElementById("filtro-ordem");

  const tbody = document.querySelector(".tabela-despesas tbody");
  const linhas = Array.from(tbody.querySelectorAll("tr"));

  // Cards de estatísticas
  const cardPendentes = document.getElementById("card-pendentes");
  const cardValorPendentes = document.getElementById("card-valor-pendentes");
  const cardPagas = document.getElementById("card-pagas");
  const cardValorPagas = document.getElementById("card-valor-pagas");
  const cardTotal = document.getElementById("card-total");

  // estado da busca
  let termoBusca = "";

  // ---------- ATUALIZAR CARDS ----------
  function atualizarCards() {
    let countPendentes = 0;
    let countPagas = 0;
    let valorPendentes = 0;
    let valorPagas = 0;
    let valorTotal = 0;

    // Contar apenas as linhas visíveis
    linhas.forEach((linha) => {
      if (linha.style.display !== "none") {
        const status = linha
          .querySelector("td:nth-child(1) span")
          .textContent.trim()
          .toLowerCase();
        const valor = parseFloat(linha.dataset.valor) || 0;

        if (status === "pendente") {
          countPendentes++;
          valorPendentes += valor;
        } else if (status === "pago") {
          countPagas++;
          valorPagas += valor;
        } else if (status === "atrasado") {
          // Atrasadas contam como pendentes
          countPendentes++;
          valorPendentes += valor;
        }

        valorTotal += valor;
      }
    });

    // Atualizar os cards
    cardPendentes.textContent = countPendentes;
    cardValorPendentes.textContent =
      "R$ " +
      valorPendentes.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    cardPagas.textContent = countPagas;
    cardValorPagas.textContent =
      "R$ " +
      valorPagas.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    cardTotal.textContent =
      "R$ " +
      valorTotal.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
  }

  // ---------- FILTROS ----------
  function aplicarFiltros() {
    const statusSel = filtroStatus.value.toLowerCase();
    const anoSel = filtroAno.value;
    const mesSel = filtroMes.value;
    const contaSel = filtroConta.value.toLowerCase();
    const formaSel = filtroForma.value.toLowerCase();
    const catSel = filtroCategoria.value.toLowerCase();
    const termo = termoBusca;

    linhas.forEach((linha) => {
      const status = linha
        .querySelector("td:nth-child(1) span")
        .textContent.trim()
        .toLowerCase();
      const [, mesStr, anoStr] = linha
        .querySelector("td:nth-child(2)")
        .textContent.trim()
        .split("/");
      const mes = parseInt(mesStr, 10),
        ano = parseInt(anoStr, 10);
      const conta = linha
        .querySelector("td:nth-child(6)")
        .textContent.trim()
        .toLowerCase();
      const forma = linha
        .querySelector("td:nth-child(7)")
        .textContent.trim()
        .toLowerCase();
      const cat = linha
        .querySelector("td:nth-child(4)")
        .textContent.trim()
        .toLowerCase();
      const texto = linha.textContent.trim().toLowerCase();

      const okStatus = !statusSel || status === statusSel;
      const okAno = !anoSel || ano === parseInt(anoSel, 10);
      const okMes = !mesSel || mes === parseInt(mesSel, 10);
      const okConta = !contaSel || conta === contaSel;

      // 👇 nova regra
      let okForma;
      if (!formaSel) {
        okForma = true; // "Todas"
      } else if (formaSel === "exceto_cartao") {
        okForma = forma !== "cartão de crédito";
      } else {
        okForma = forma === formaSel;
      }

      const okCat = !catSel || cat === catSel;
      const okBusca = !termo || texto.includes(termo);

      linha.style.display =
        okStatus && okAno && okMes && okConta && okForma && okCat && okBusca
          ? ""
          : "none";
    });

    // Atualizar cards após aplicar filtros
    atualizarCards();

    // depois que filtra, aplica a ordenação
    aplicarOrdenacao();
  }

  // ---------- ORDENAÇÃO ----------
  function cmpStr(a, b) {
    if (a === b) return 0;
    return a > b ? 1 : -1;
  }

  function cmpNum(a, b) {
    return a - b;
  }

  function aplicarOrdenacao() {
    const valor = selectOrdem?.value || "criado_desc";

    // pega TODAS as linhas (ordenar todas mantém a ordem mesmo quando reexibidas)
    const rows = Array.from(tbody.querySelectorAll("tr"));

    rows.sort((ra, rb) => {
      switch (valor) {
        case "venc_asc":
          return cmpStr(
            ra.dataset.vencimento || "",
            rb.dataset.vencimento || ""
          );
        case "venc_desc":
          return cmpStr(
            rb.dataset.vencimento || "",
            ra.dataset.vencimento || ""
          );
        case "criado_asc":
          return cmpStr(ra.dataset.criado || "", rb.dataset.criado || "");
        case "criado_desc":
          return cmpStr(rb.dataset.criado || "", ra.dataset.criado || "");
        case "valor_asc":
          return cmpNum(
            parseFloat(ra.dataset.valor || "0"),
            parseFloat(rb.dataset.valor || "0")
          );
        case "valor_desc":
          return cmpNum(
            parseFloat(rb.dataset.valor || "0"),
            parseFloat(ra.dataset.valor || "0")
          );
        case "desc_asc": {
          const ta =
            ra
              .querySelector("td:nth-child(3)")
              ?.textContent.trim()
              .toLowerCase() || "";
          const tb =
            rb
              .querySelector("td:nth-child(3)")
              ?.textContent.trim()
              .toLowerCase() || "";
          return cmpStr(ta, tb);
        }
        case "desc_desc": {
          const ta =
            ra
              .querySelector("td:nth-child(3)")
              ?.textContent.trim()
              .toLowerCase() || "";
          const tb =
            rb
              .querySelector("td:nth-child(3)")
              ?.textContent.trim()
              .toLowerCase() || "";
          return cmpStr(tb, ta);
        }
        default:
          return 0;
      }
    });

    // reanexa na nova ordem
    rows.forEach((r) => tbody.appendChild(r));
  }

  // listeners
  [
    filtroStatus,
    filtroAno,
    filtroMes,
    filtroConta,
    filtroForma,
    filtroCategoria,
  ].forEach((el) => el.addEventListener("change", aplicarFiltros));

  inputBusca.addEventListener("input", (e) => {
    termoBusca = e.target.value.toLowerCase();
    aplicarFiltros();
  });

  selectOrdem?.addEventListener("change", aplicarOrdenacao);

  // inicial
  aplicarFiltros();
});
