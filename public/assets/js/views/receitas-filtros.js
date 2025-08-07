document.addEventListener("DOMContentLoaded", () => {
  const filtroStatus = document.getElementById("filtro-status");
  const filtroMes = document.getElementById("filtro-mes");
  const filtroConta = document.getElementById("filtro-conta");
  const filtroForma = document.getElementById("filtro-forma");
  const filtroCategoria = document.getElementById("filtro-categoria");

  const linhas = document.querySelectorAll(".tabela-receitas tbody tr");

  function aplicarFiltros() {
    const statusSelecionado = filtroStatus.value.toLowerCase();
    const mesSelecionado = filtroMes.value;
    const contaSelecionada = filtroConta.value.toLowerCase();
    const formaSelecionada = filtroForma.value.toLowerCase();
    const categoriaSelecionada = filtroCategoria.value.toLowerCase();

    linhas.forEach((linha) => {
      const status = linha
        .querySelector("td:nth-child(1) span")
        .textContent.trim()
        .toLowerCase();
      const data = linha
        .querySelector("td:nth-child(2)")
        .textContent.trim()
        .split("/");
      const mes = parseInt(data[1]);

      const descricao = linha
        .querySelector("td:nth-child(3)")
        .textContent.trim()
        .toLowerCase();
      const categoria = linha
        .querySelector("td:nth-child(4)")
        .textContent.trim()
        .toLowerCase();
      const conta = linha
        .querySelector("td:nth-child(6)")
        .textContent.trim()
        .toLowerCase();
      const forma = linha
        .querySelector("td:nth-child(7)")
        .textContent.trim()
        .toLowerCase();

      const statusOK = !statusSelecionado || status === statusSelecionado;
      const mesOK = !mesSelecionado || parseInt(mesSelecionado) === mes;
      const contaOK = !contaSelecionada || conta === contaSelecionada;
      const formaOK = !formaSelecionada || forma === formaSelecionada;
      const categoriaOK =
        !categoriaSelecionada || categoria === categoriaSelecionada;

      linha.style.display =
        statusOK && mesOK && contaOK && formaOK && categoriaOK ? "" : "none";
    });
  }

  [filtroStatus, filtroMes, filtroConta, filtroForma, filtroCategoria].forEach(
    (filtro) => filtro.addEventListener("change", aplicarFiltros)
  );

  aplicarFiltros();
});
