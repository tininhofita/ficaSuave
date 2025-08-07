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

  document.querySelectorAll(".despesas tbody .categoria-row").forEach((row) => {
    row.addEventListener("mouseenter", (e) => {
      const subcats = JSON.parse(row.dataset.subcats);
      if (!subcats.length) return;

      // gera mini-tabela
      let html = `<table class="tt-table">
        <thead><tr>
          <th>Subcat.</th><th>Valor</th><th>%</th>
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

      // posiciona à direita da linha
      const rect = row.getBoundingClientRect();
      tooltip.style.top = `${rect.top + window.scrollY}px`;
      tooltip.style.left = `${rect.right + 10 + window.scrollX}px`;
    });

    row.addEventListener("mouseleave", () => {
      tooltip.style.display = "none";
      tooltip.innerHTML = "";
    });
  });

  new Chart(ctx, config);
});
