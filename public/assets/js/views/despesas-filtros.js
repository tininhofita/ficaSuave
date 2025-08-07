document.addEventListener("DOMContentLoaded", () => {
  const filtroStatus = document.getElementById("filtro-status");
  const filtroAno = document.getElementById("filtro-ano");
  const filtroMes = document.getElementById("filtro-mes");
  const filtroConta = document.getElementById("filtro-conta");
  const filtroForma = document.getElementById("filtro-forma");
  const filtroCategoria = document.getElementById("filtro-categoria");
  const inputBusca = document.getElementById("filtro-busca");

  const linhas = Array.from(
    document.querySelectorAll(".tabela-despesas tbody tr")
  );

  // variáveis de estado
  let termoBusca = "";

  function aplicarFiltros() {
    const statusSel = filtroStatus.value.toLowerCase();
    const anoSel = filtroAno.value;
    const mesSel = filtroMes.value;
    const contaSel = filtroConta.value.toLowerCase();
    const formaSel = filtroForma.value.toLowerCase();
    const catSel = filtroCategoria.value.toLowerCase();
    const termo = termoBusca;

    linhas.forEach((linha) => {
      // extrai campos
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

      // condições
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
  }

  // dispara ao mudar qualquer filtro
  [
    filtroStatus,
    filtroAno,
    filtroMes,
    filtroConta,
    filtroForma,
    filtroCategoria,
  ].forEach((el) => el.addEventListener("change", aplicarFiltros));

  // dispara na digitação, atualiza termoBusca e reaplica
  inputBusca.addEventListener("input", (e) => {
    termoBusca = e.target.value.toLowerCase();
    aplicarFiltros();
  });

  // aplica ao carregar
  aplicarFiltros();
});
