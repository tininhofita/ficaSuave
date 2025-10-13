document.addEventListener("DOMContentLoaded", () => {
  const filtroStatus = document.getElementById("filtro-status");
  const filtroAno = document.getElementById("filtro-ano");
  const filtroMes = document.getElementById("filtro-mes");
  const filtroConta = document.getElementById("filtro-conta");
  const filtroForma = document.getElementById("filtro-forma");
  const filtroCategoria = document.getElementById("filtro-categoria");
  const inputBusca = document.getElementById("filtro-busca");
  const selectOrdem = document.getElementById("filtro-ordem");

  const tbody = document.querySelector(".tabela-receitas tbody");
  const linhas = Array.from(tbody.querySelectorAll("tr"));

  // Cards de estatísticas
  const cardPrevistas = document.getElementById("card-previstas");
  const cardValorPrevistas = document.getElementById("card-valor-previstas");
  const cardRecebidas = document.getElementById("card-recebidas");
  const cardValorRecebidas = document.getElementById("card-valor-recebidas");
  const cardTotal = document.getElementById("card-total");

  // estado da busca
  let termoBusca = "";

  // ---------- ATUALIZAR CARDS ----------
  function atualizarCards() {
    let countRecebidas = 0;
    let countPrevistas = 0;
    let valorPrevistas = 0;
    let valorRecebidas = 0;
    let valorTotal = 0;

    // Contar apenas as linhas visíveis
    linhas.forEach((linha) => {
      if (linha.style.display !== "none") {
        const status = linha.dataset.status;
        const valor = parseFloat(linha.dataset.valor) || 0;

        if (status === "recebido") {
          countRecebidas++;
          valorRecebidas += valor;
        } else if (status === "previsto") {
          countPrevistas++;
          valorPrevistas += valor;
        } else if (status === "atrasado") {
          // Atrasadas contam como previstas
          countPrevistas++;
          valorPrevistas += valor;
        }

        valorTotal += valor;
      }
    });

    // Atualizar os cards
    cardPrevistas.textContent = countPrevistas;
    cardValorPrevistas.textContent =
      "R$ " +
      valorPrevistas.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    cardRecebidas.textContent = countRecebidas;
    cardValorRecebidas.textContent =
      "R$ " +
      valorRecebidas.toLocaleString("pt-BR", {
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

      let mostrar = true;

      // Filtro por status
      if (statusSel && status !== statusSel) {
        mostrar = false;
      }

      // Filtro por ano
      if (anoSel && ano.toString() !== anoSel) {
        mostrar = false;
      }

      // Filtro por mês
      if (mesSel && mes.toString() !== mesSel) {
        mostrar = false;
      }

      // Filtro por conta
      if (contaSel && !conta.includes(contaSel)) {
        mostrar = false;
      }

      // Filtro por forma
      if (formaSel && !forma.includes(formaSel)) {
        mostrar = false;
      }

      // Filtro por categoria
      if (catSel && !cat.includes(catSel)) {
        mostrar = false;
      }

      // Filtro por busca
      if (termo && !texto.includes(termo)) {
        mostrar = false;
      }

      linha.style.display = mostrar ? "" : "none";
    });

    // Atualizar cards após aplicar filtros
    atualizarCards();
  }

  // ---------- ORDENAÇÃO ----------
  function ordenarTabela() {
    const ordem = selectOrdem.value;
    const linhasVisiveis = linhas.filter(
      (linha) => linha.style.display !== "none"
    );

    linhasVisiveis.sort((a, b) => {
      switch (ordem) {
        case "venc_asc":
          return (
            new Date(a.dataset.vencimento) - new Date(b.dataset.vencimento)
          );
        case "venc_desc":
          return (
            new Date(b.dataset.vencimento) - new Date(a.dataset.vencimento)
          );
        case "criado_asc":
          return new Date(a.dataset.criado) - new Date(b.dataset.criado);
        case "criado_desc":
          return new Date(b.dataset.criado) - new Date(a.dataset.criado);
        case "valor_asc":
          return parseFloat(a.dataset.valor) - parseFloat(b.dataset.valor);
        case "valor_desc":
          return parseFloat(b.dataset.valor) - parseFloat(a.dataset.valor);
        case "desc_asc":
          return a
            .querySelector("td:nth-child(3)")
            .textContent.localeCompare(
              b.querySelector("td:nth-child(3)").textContent
            );
        case "desc_desc":
          return b
            .querySelector("td:nth-child(3)")
            .textContent.localeCompare(
              a.querySelector("td:nth-child(3)").textContent
            );
        default:
          return 0;
      }
    });

    // Reordenar as linhas no DOM
    linhasVisiveis.forEach((linha) => {
      tbody.appendChild(linha);
    });
  }

  // ---------- EVENT LISTENERS ----------
  filtroStatus.addEventListener("change", aplicarFiltros);
  filtroAno.addEventListener("change", aplicarFiltros);
  filtroMes.addEventListener("change", aplicarFiltros);
  filtroConta.addEventListener("change", aplicarFiltros);
  filtroForma.addEventListener("change", aplicarFiltros);
  filtroCategoria.addEventListener("change", aplicarFiltros);
  selectOrdem.addEventListener("change", () => {
    ordenarTabela();
  });

  // Busca com debounce
  let timeoutBusca;
  inputBusca.addEventListener("input", (e) => {
    clearTimeout(timeoutBusca);
    timeoutBusca = setTimeout(() => {
      termoBusca = e.target.value.toLowerCase();
      aplicarFiltros();
    }, 300);
  });

  // Aplicar filtros iniciais
  aplicarFiltros();
});
