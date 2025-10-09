// Filtros de mês e ano
function aplicarFiltro(mes, ano) {
  const url = new URL(window.location);
  url.searchParams.set("mes", mes);
  url.searchParams.set("ano", ano);
  window.location.href = url.toString();
}

// Event listeners para os filtros
document.addEventListener("DOMContentLoaded", () => {
  // Filtros de mês
  document.querySelectorAll("[data-mes]").forEach((button) => {
    button.addEventListener("click", (e) => {
      // Remove classe ativo de todos os botões de mês
      document
        .querySelectorAll("[data-mes]")
        .forEach((btn) => btn.classList.remove("ativo"));
      // Adiciona classe ativo ao botão clicado
      e.target.classList.add("ativo");

      const mes = e.target.dataset.mes;
      const ano =
        document.querySelector("[data-ano].ativo")?.dataset.ano ||
        new Date().getFullYear();

      aplicarFiltro(mes, ano);
    });
  });

  // Filtros de ano
  document.querySelectorAll("[data-ano]").forEach((button) => {
    button.addEventListener("click", (e) => {
      // Remove classe ativo de todos os botões de ano
      document
        .querySelectorAll("[data-ano]")
        .forEach((btn) => btn.classList.remove("ativo"));
      // Adiciona classe ativo ao botão clicado
      e.target.classList.add("ativo");

      const ano = e.target.dataset.ano;
      const mes =
        document.querySelector("[data-mes].ativo")?.dataset.mes ||
        String(new Date().getMonth() + 1).padStart(2, "0");

      aplicarFiltro(mes, ano);
    });
  });

  // Grafico de Receitas e Despesas
  const canvas = document.getElementById("receitas-despesas");
  const ctx = canvas.getContext("2d");
  const receitasPorMes = JSON.parse(canvas.dataset.receitas);
  const despesasPorMes = JSON.parse(canvas.dataset.despesas);

  const dados = {
    labels: [
      "Jan",
      "Fev",
      "Mar",
      "Abr",
      "Mai",
      "Jun",
      "Jul",
      "Ago",
      "Set",
      "Out",
      "Nov",
      "Dez",
    ],
    datasets: [
      {
        label: "Receitas",
        data: receitasPorMes,
        backgroundColor: "rgba(46, 204, 113, 0.7)",
        borderRadius: 6,
      },
      {
        label: "Despesas",
        data: despesasPorMes,
        backgroundColor: "rgba(231, 76, 60, 0.7)",
        borderRadius: 6,
      },
    ],
  };

  const config = {
    type: "bar",
    data: dados,
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "top",
          labels: {
            font: {
              family: "Poppins",
            },
          },
        },
        title: {
          display: true,
          text: "Receitas x Despesas por Mês",
          font: {
            size: 18,
            family: "Montserrat",
          },
        },
        datalabels: {
          color: "#333",
          anchor: "end",
          align: "top",
          font: {
            weight: "bold",
            size: 12,
            family: "Roboto",
          },
          formatter: function (value) {
            return `R$ ${value.toLocaleString("pt-BR")}`;
          },
        },
      },
      scales: {
        x: {
          stacked: false,
          ticks: {
            font: {
              family: "Roboto",
            },
          },
        },
        y: {
          beginAtZero: true,
          ticks: {
            font: {
              family: "Roboto",
            },
          },
        },
      },
    },
  };

  const tooltip = document.getElementById("tooltip");

  // Função para posicionar tooltip
  function positionTooltip(tooltip, row) {
    const rect = row.getBoundingClientRect();
    const tooltipRect = tooltip.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    let left = rect.right + 10;
    let top = rect.top + window.scrollY;

    // Ajusta se sair da tela à direita
    if (left + tooltipRect.width > viewportWidth) {
      left = rect.left - tooltipRect.width - 10;
    }

    // Ajusta se sair da tela em baixo
    if (top + tooltipRect.height > viewportHeight + window.scrollY) {
      top = viewportHeight + window.scrollY - tooltipRect.height - 10;
    }

    // Ajusta se sair da tela em cima
    if (top < window.scrollY) {
      top = window.scrollY + 10;
    }

    tooltip.style.left = `${left + window.scrollX}px`;
    tooltip.style.top = `${top}px`;
  }

  // Tooltips para despesas por categoria
  const categoriaRows = document.querySelectorAll(
    ".modern-table tbody .categoria-row"
  );
  console.log("Linhas com subcategorias encontradas:", categoriaRows.length);

  categoriaRows.forEach((row) => {
    row.addEventListener("mouseenter", (e) => {
      const subcats = JSON.parse(row.dataset.subcats);
      console.log("Subcategorias encontradas:", subcats);

      if (!subcats.length) {
        // Se não tem subcategorias, mostra informações básicas
        const cells = row.querySelectorAll("td");
        let html = `<table class="tt-table">
          <thead><tr>
            <th>Informação</th><th>Valor</th>
          </tr></thead><tbody>
          <tr><td>Categoria</td><td>${cells[0].textContent}</td></tr>
          <tr><td>Valor Total</td><td>${cells[1].textContent}</td></tr>
          <tr><td>Percentual</td><td>${cells[2].textContent}</td></tr>
          </tbody></table>`;

        tooltip.innerHTML = html;
        tooltip.style.display = "block";
        setTimeout(() => positionTooltip(tooltip, row), 10);
        return;
      }

      // Determina se é uma linha de cartão (categorias) ou despesas (subcategorias)
      const isCartaoRow = row.classList.contains("cartao-row");
      const itemType = isCartaoRow ? "Categoria" : "Subcategoria";
      const itemName = isCartaoRow ? "nome_categoria" : "nome_subcategoria";

      // gera mini-tabela com categorias ou subcategorias
      let html = `<table class="tt-table">
        <thead><tr>
          <th>${itemType}</th><th>Valor</th><th>%</th>
        </tr></thead><tbody>`;
      subcats.forEach((item) => {
        html += `<tr>
          <td>${item[itemName]}</td>
          <td>R$ ${Number(item.total).toLocaleString("pt-BR", {
            minimumFractionDigits: 2,
          })}</td>
          <td>${Math.round(item.percentual)}%</td>
        </tr>`;
      });
      html += `</tbody></table>`;

      tooltip.innerHTML = html;
      tooltip.style.display = "block";

      // Posiciona o tooltip
      setTimeout(() => positionTooltip(tooltip, row), 10);
    });

    row.addEventListener("mouseleave", () => {
      tooltip.style.display = "none";
      tooltip.innerHTML = "";
    });
  });

  // Tooltips para todas as outras linhas das tabelas
  document.querySelectorAll(".modern-table tbody tr").forEach((row) => {
    // Pula as linhas que já têm tooltip (categoria-row)
    if (row.classList.contains("categoria-row")) return;

    row.addEventListener("mouseenter", (e) => {
      const cells = row.querySelectorAll("td");
      if (cells.length < 3) return;

      let html = `<table class="tt-table">
        <thead><tr>
          <th>Informação</th><th>Valor</th>
        </tr></thead><tbody>`;

      // Determina o tipo de informação baseado no conteúdo
      const firstCell = cells[0].textContent.trim();
      const secondCell = cells[1].textContent.trim();
      const thirdCell = cells[2].textContent.trim();

      html += `<tr><td>Item</td><td>${firstCell}</td></tr>`;
      html += `<tr><td>Valor Total</td><td>${secondCell}</td></tr>`;
      html += `<tr><td>Percentual</td><td>${thirdCell}</td></tr>`;

      html += `</tbody></table>`;

      tooltip.innerHTML = html;
      tooltip.style.display = "block";

      setTimeout(() => positionTooltip(tooltip, row), 10);
    });

    row.addEventListener("mouseleave", () => {
      tooltip.style.display = "none";
      tooltip.innerHTML = "";
    });
  });

  new Chart(ctx, config);

  // ===== WIDGETS INTERATIVOS =====

  // Gráfico de Pizza - Gastos por Categoria
  const pizzaCtx = document.getElementById("gastos-pizza");
  if (pizzaCtx) {
    const categoriasData = JSON.parse(pizzaCtx.dataset.categorias);

    const pizzaChart = new Chart(pizzaCtx.getContext("2d"), {
      type: "doughnut",
      data: {
        labels: categoriasData.map((cat) => cat.nome_categoria),
        datasets: [
          {
            data: categoriasData.map((cat) => cat.total),
            backgroundColor: [
              "#2ecc71",
              "#3498db",
              "#e74c3c",
              "#f39c12",
              "#9b59b6",
              "#1abc9c",
              "#34495e",
              "#e67e22",
              "#95a5a6",
              "#f1c40f",
              "#e91e63",
              "#ff5722",
            ],
            borderWidth: 2,
            borderColor: "#ffffff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "right",
            labels: {
              usePointStyle: true,
              padding: 20,
              font: {
                size: 12,
              },
            },
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const total = categoriasData.reduce(
                  (sum, cat) => sum + cat.total,
                  0
                );
                const percentage = ((context.parsed * 100) / total).toFixed(1);
                return `${context.label}: R$ ${context.parsed.toLocaleString(
                  "pt-BR"
                )} (${percentage}%)`;
              },
            },
          },
        },
        cutout: "60%",
      },
    });

    // Toggle para mostrar/esconder gráfico de pizza
    document
      .getElementById("toggle-pizza")
      ?.addEventListener("click", function () {
        const chartContainer = pizzaCtx.closest(".chart-container");
        const isVisible = chartContainer.style.display !== "none";
        chartContainer.style.display = isVisible ? "none" : "block";
        this.innerHTML = isVisible
          ? '<i class="ph ph-eye-slash"></i>'
          : '<i class="ph ph-eye"></i>';
      });
  }

  // Gráfico de Barras - Gastos por Cartão
  const barrasCtx = document.getElementById("cartoes-barras");
  if (barrasCtx) {
    const cartoesData = JSON.parse(barrasCtx.dataset.cartoes);

    const barrasChart = new Chart(barrasCtx.getContext("2d"), {
      type: "bar",
      data: {
        labels: cartoesData.map((cartao) => cartao.nome_cartao),
        datasets: [
          {
            label: "Gastos (R$)",
            data: cartoesData.map((cartao) => cartao.total),
            backgroundColor: cartoesData.map(
              (_, index) =>
                `hsl(${(index * 360) / cartoesData.length}, 70%, 60%)`
            ),
            borderRadius: 8,
            borderSkipped: false,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              title: function (context) {
                return context[0].label;
              },
              label: function (context) {
                return `Gastos: R$ ${context.parsed.y.toLocaleString("pt-BR")}`;
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return "R$ " + value.toLocaleString("pt-BR");
              },
            },
          },
          x: {
            ticks: {
              maxRotation: 45,
              minRotation: 45,
            },
          },
        },
      },
    });

    // Toggle para mostrar/esconder gráfico de barras
    document
      .getElementById("toggle-barras")
      ?.addEventListener("click", function () {
        const chartContainer = barrasCtx.closest(".chart-container");
        const isVisible = chartContainer.style.display !== "none";
        chartContainer.style.display = isVisible ? "none" : "block";
        this.innerHTML = isVisible
          ? '<i class="ph ph-eye-slash"></i>'
          : '<i class="ph ph-eye"></i>';
      });
  }

  // Filtros Avançados
  const dataInicioInput = document.getElementById("data-inicio");
  const dataFimInput = document.getElementById("data-fim");
  const aplicarPeriodoBtn = document.getElementById("aplicar-periodo");
  const tipoComparacaoSelect = document.getElementById("tipo-comparacao");

  // Define datas padrão (mês atual)
  const hoje = new Date();
  const primeiroDiaMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
  const ultimoDiaMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);

  if (dataInicioInput && dataFimInput) {
    dataInicioInput.value = primeiroDiaMes.toISOString().split("T")[0];
    dataFimInput.value = ultimoDiaMes.toISOString().split("T")[0];
  }

  // Aplicar período personalizado
  aplicarPeriodoBtn?.addEventListener("click", function () {
    const dataInicio = dataInicioInput.value;
    const dataFim = dataFimInput.value;

    if (!dataInicio || !dataFim) {
      alert("Por favor, selecione as duas datas");
      return;
    }

    if (new Date(dataInicio) > new Date(dataFim)) {
      alert("A data de início deve ser anterior à data de fim");
      return;
    }

    // Simular carregamento
    this.innerHTML = '<i class="ph ph-spinner"></i> Carregando...';
    this.disabled = true;

    setTimeout(() => {
      this.innerHTML = "Aplicar";
      this.disabled = false;
      // Aqui você recarregaria a página com os novos parâmetros
      console.log("Período selecionado:", dataInicio, "até", dataFim);
    }, 1500);
  });

  // Mudança no tipo de comparação
  tipoComparacaoSelect?.addEventListener("change", function () {
    console.log("Tipo de comparação:", this.value);
    // Aqui você pode implementar a lógica para alterar os dados
    // dos gráficos baseado no tipo de comparação selecionado
  });

  // ===== NAVEGAÇÃO LATERAL =====
  const navLateral = document.getElementById("nav-lateral");
  const navToggle = document.getElementById("nav-toggle");
  const navLinks = document.querySelectorAll(".nav-link");

  // Toggle do menu lateral
  navToggle?.addEventListener("click", function () {
    navLateral?.classList.toggle("active");
  });

  // Fechar menu ao clicar fora
  document.addEventListener("click", function (e) {
    if (!navLateral?.contains(e.target)) {
      navLateral?.classList.remove("active");
    }
  });

  // Scroll suave para seções
  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href");
      const targetElement = document.querySelector(targetId);

      if (targetElement) {
        targetElement.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });

        // Fechar menu após clique
        navLateral?.classList.remove("active");
      }
    });
  });

  // ===== BOTÃO VOLTAR AO TOPO =====
  const backToTop = document.getElementById("back-to-top");

  // Mostrar/ocultar botão baseado no scroll
  window.addEventListener("scroll", function () {
    if (window.pageYOffset > 300) {
      backToTop?.classList.add("visible");
    } else {
      backToTop?.classList.remove("visible");
    }
  });

  // Voltar ao topo
  backToTop?.addEventListener("click", function () {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });

  // ===== HIGHLIGHT DA SEÇÃO ATIVA =====
  const sections = document.querySelectorAll("section[id], div[id]");

  function highlightActiveSection() {
    let current = "";

    sections.forEach((section) => {
      const sectionTop = section.offsetTop - 100;
      const sectionHeight = section.clientHeight;

      if (
        window.pageYOffset >= sectionTop &&
        window.pageYOffset < sectionTop + sectionHeight
      ) {
        current = section.getAttribute("id");
      }
    });

    // Atualizar link ativo
    navLinks.forEach((link) => {
      link.classList.remove("active");
      if (link.getAttribute("href") === `#${current}`) {
        link.classList.add("active");
      }
    });
  }

  // Executar highlight no scroll
  window.addEventListener("scroll", highlightActiveSection);

  // ===== NOVOS GRÁFICOS =====

  // Gráfico de Área - Receitas vs Despesas
  const areaCtx = document.getElementById("receitas-despesas-area");
  if (areaCtx) {
    const receitasArea = JSON.parse(areaCtx.dataset.receitas);
    const despesasArea = JSON.parse(areaCtx.dataset.despesas);

    const areaChart = new Chart(areaCtx.getContext("2d"), {
      type: "line",
      data: {
        labels: [
          "Jan",
          "Fev",
          "Mar",
          "Abr",
          "Mai",
          "Jun",
          "Jul",
          "Ago",
          "Set",
          "Out",
          "Nov",
          "Dez",
        ],
        datasets: [
          {
            label: "Receitas",
            data: receitasArea.map((item) => item.total),
            borderColor: "#2ecc71",
            backgroundColor: "rgba(46, 204, 113, 0.3)",
            fill: true,
            tension: 0.4,
          },
          {
            label: "Despesas",
            data: despesasArea.map((item) => item.total),
            borderColor: "#e74c3c",
            backgroundColor: "rgba(231, 76, 60, 0.3)",
            fill: true,
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "top",
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return "R$ " + value.toLocaleString("pt-BR");
              },
            },
          },
        },
      },
    });

    // Toggle para mostrar/esconder gráfico de área
    document
      .getElementById("toggle-area")
      ?.addEventListener("click", function () {
        const chartContainer = areaCtx.closest(".chart-container");
        const isVisible = chartContainer.style.display !== "none";
        chartContainer.style.display = isVisible ? "none" : "block";
        this.innerHTML = isVisible
          ? '<i class="ph ph-eye-slash"></i>'
          : '<i class="ph ph-eye"></i>';
      });
  }

  // Gráfico de Linha - Tendência de Poupança
  const tendenciaCtx = document.getElementById("tendencia-poupanca");
  if (tendenciaCtx) {
    const taxaPoupanca = JSON.parse(tendenciaCtx.dataset.taxa);

    // Simular dados de tendência (você pode implementar dados reais aqui)
    const meses = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun"];
    const tendenciaData = [5, 8, 12, 15, 18, taxaPoupanca || 20];

    const tendenciaChart = new Chart(tendenciaCtx.getContext("2d"), {
      type: "line",
      data: {
        labels: meses,
        datasets: [
          {
            label: "Taxa de Poupança (%)",
            data: tendenciaData,
            borderColor: "#27ae60",
            backgroundColor: "rgba(39, 174, 96, 0.1)",
            fill: true,
            tension: 0.4,
            pointBackgroundColor: "#27ae60",
            pointBorderColor: "#fff",
            pointBorderWidth: 2,
            pointRadius: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: function (value) {
                return value + "%";
              },
            },
          },
        },
      },
    });

    // Toggle para mostrar/esconder gráfico de tendência
    document
      .getElementById("toggle-tendencia")
      ?.addEventListener("click", function () {
        const chartContainer = tendenciaCtx.closest(".chart-container");
        const isVisible = chartContainer.style.display !== "none";
        chartContainer.style.display = isVisible ? "none" : "block";
        this.innerHTML = isVisible
          ? '<i class="ph ph-eye-slash"></i>'
          : '<i class="ph ph-eye"></i>';
      });
  }

  // Gráfico de Radar - Utilização de Cartões
  const radarCtx = document.getElementById("utilizacao-cartoes");
  if (radarCtx) {
    const cartoesData = JSON.parse(radarCtx.dataset.cartoes);

    const radarChart = new Chart(radarCtx.getContext("2d"), {
      type: "radar",
      data: {
        labels: cartoesData.map((cartao) => cartao.nome_cartao),
        datasets: [
          {
            label: "Utilização (%)",
            data: cartoesData.map((cartao) => cartao.utilizacao_percentual),
            borderColor: "#3498db",
            backgroundColor: "rgba(52, 152, 219, 0.2)",
            pointBackgroundColor: "#3498db",
            pointBorderColor: "#fff",
            pointBorderWidth: 2,
            pointRadius: 5,
          },
          {
            label: "Limite Disponível (%)",
            data: cartoesData.map(
              (cartao) => 100 - cartao.utilizacao_percentual
            ),
            borderColor: "#2ecc71",
            backgroundColor: "rgba(46, 204, 113, 0.2)",
            pointBackgroundColor: "#2ecc71",
            pointBorderColor: "#fff",
            pointBorderWidth: 2,
            pointRadius: 5,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
          },
        },
        scales: {
          r: {
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: function (value) {
                return value + "%";
              },
            },
          },
        },
      },
    });

    // Toggle para mostrar/esconder gráfico de radar
    document
      .getElementById("toggle-radar")
      ?.addEventListener("click", function () {
        const chartContainer = radarCtx.closest(".chart-container");
        const isVisible = chartContainer.style.display !== "none";
        chartContainer.style.display = isVisible ? "none" : "block";
        this.innerHTML = isVisible
          ? '<i class="ph ph-eye-slash"></i>'
          : '<i class="ph ph-eye"></i>';
      });
  }

  // Gráfico de Doughnut - Gastos por Bandeira
  const bandeirasCtx = document.getElementById("gastos-bandeiras");
  if (bandeirasCtx) {
    const bandeirasData = JSON.parse(bandeirasCtx.dataset.bandeiras);

    const bandeirasChart = new Chart(bandeirasCtx.getContext("2d"), {
      type: "doughnut",
      data: {
        labels: bandeirasData.map((bandeira) => bandeira.bandeira),
        datasets: [
          {
            data: bandeirasData.map((bandeira) => bandeira.total_gasto),
            backgroundColor: [
              "#e74c3c",
              "#3498db",
              "#f39c12",
              "#2ecc71",
              "#9b59b6",
              "#1abc9c",
              "#34495e",
              "#e67e22",
            ],
            borderWidth: 3,
            borderColor: "#ffffff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
              font: {
                size: 12,
              },
            },
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const total = bandeirasData.reduce(
                  (sum, bandeira) => sum + bandeira.total_gasto,
                  0
                );
                const percentage = ((context.parsed * 100) / total).toFixed(1);
                return `${context.label}: R$ ${context.parsed.toLocaleString(
                  "pt-BR"
                )} (${percentage}%)`;
              },
            },
          },
        },
        cutout: "50%",
      },
    });

    // Toggle para mostrar/esconder gráfico de bandeiras
    document
      .getElementById("toggle-bandeiras")
      ?.addEventListener("click", function () {
        const chartContainer = bandeirasCtx.closest(".chart-container");
        const isVisible = chartContainer.style.display !== "none";
        chartContainer.style.display = isVisible ? "none" : "block";
        this.innerHTML = isVisible
          ? '<i class="ph ph-eye-slash"></i>'
          : '<i class="ph ph-eye"></i>';
      });
  }
});
