// Grafico de Receitas e Despesas
document.addEventListener("DOMContentLoaded", () => {
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

      // gera mini-tabela com subcategorias
      let html = `<table class="tt-table">
        <thead><tr>
          <th>Subcategoria</th><th>Valor</th><th>%</th>
        </tr></thead><tbody>`;
      subcats.forEach((item) => {
        html += `<tr>
          <td>${item.nome_subcategoria}</td>
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
});
