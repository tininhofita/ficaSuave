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

  // estado da busca
  let termoBusca = "";

  // ---------- FILTROS ----------
  function aplicarFiltros() {
    const statusSel = (filtroStatus.value || "").toLowerCase();
    const anoSel = filtroAno.value;
    const mesSel = filtroMes.value;
    const contaSel = (filtroConta.value || "").toLowerCase();
    const formaSel = (filtroForma.value || "").toLowerCase();
    const catSel = (filtroCategoria.value || "").toLowerCase();
    const termo = termoBusca;

    linhas.forEach((linha) => {
      const status = linha
        .querySelector("td:nth-child(1) span")
        .textContent.trim()
        .toLowerCase();

      // coluna 2 tem dd/mm/YYYY
      const [, mesStr, anoStr] = linha
        .querySelector("td:nth-child(2)")
        .textContent.trim()
        .split("/");
      const mes = parseInt(mesStr, 10);
      const ano = parseInt(anoStr, 10);

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
      const okForma = !formaSel || forma === formaSel;
      const okCat = !catSel || cat === catSel;
      const okBusca = !termo || texto.includes(termo);

      linha.style.display =
        okStatus && okAno && okMes && okConta && okForma && okCat && okBusca
          ? ""
          : "none";
    });

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
    const valor = selectOrdem?.value || "venc_asc";

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
