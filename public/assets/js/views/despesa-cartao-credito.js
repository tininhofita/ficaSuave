document.addEventListener("DOMContentLoaded", function () {
  // -------- Variáveis globais do escopo --------
  const urlParams = new URLSearchParams(window.location.search);
  let mes = parseInt(urlParams.get("mes")) || new Date().getMonth() + 1;
  let ano = parseInt(urlParams.get("ano")) || new Date().getFullYear();

  // -------- Navegação de Mês --------
  const mesSpan = document.querySelector(".filtro-mes span");
  const btnAnterior = document.querySelector(".filtro-mes button:first-child");
  const btnProximo = document.querySelector(".filtro-mes button:last-child");

  if (mesSpan && btnAnterior && btnProximo) {
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

    btnAnterior.addEventListener("click", () => {
      dataAtual.setMonth(dataAtual.getMonth() - 1);
      carregarFaturaDoMes();
    });

    btnProximo.addEventListener("click", () => {
      dataAtual.setMonth(dataAtual.getMonth() + 1);
      carregarFaturaDoMes();
    });

    atualizarTextoMes();
  }

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
    const btnText = btn.querySelector(".btn-text");
    const originalText = btnText ? btnText.textContent : btn.textContent;

    btn.disabled = true;
    if (btnText) {
      btnText.textContent = "Processando...";
    } else {
      btn.textContent = "Processando...";
    }

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
      if (btnText) {
        btnText.textContent = originalText;
      } else {
        btn.textContent = originalText;
      }
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

  // ===== GRÁFICOS INTERATIVOS =====

  // Gráfico de Distribuição por Categoria (Pizza)
  const categoriasCanvas = document.getElementById("grafico-categorias");
  if (categoriasCanvas) {
    try {
      const categoriasData = JSON.parse(categoriasCanvas.dataset.categorias);
      console.log("Dados categorias:", categoriasData);

      if (categoriasData && categoriasData.length > 0) {
        const categoriasChart = new Chart(categoriasCanvas.getContext("2d"), {
          type: "doughnut",
          data: {
            labels: categoriasData.map((item) => item.categoria),
            datasets: [
              {
                data: categoriasData.map((item) =>
                  parseFloat(item.total_gasto)
                ),
                backgroundColor: [
                  "#3498db",
                  "#e74c3c",
                  "#f39c12",
                  "#2ecc71",
                  "#9b59b6",
                  "#1abc9c",
                  "#34495e",
                  "#e67e22",
                  "#f1c40f",
                  "#e91e63",
                  "#607d8b",
                  "#ff9800",
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
                position: "bottom",
                labels: {
                  usePointStyle: true,
                  padding: 15,
                  font: {
                    size: 11,
                  },
                },
              },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    const total = categoriasData.reduce(
                      (sum, item) => sum + parseFloat(item.total_gasto),
                      0
                    );
                    const percentage = ((context.parsed * 100) / total).toFixed(
                      1
                    );
                    return `${
                      context.label
                    }: R$ ${context.parsed.toLocaleString(
                      "pt-BR"
                    )} (${percentage}%)`;
                  },
                },
              },
            },
            cutout: "60%",
          },
        });
      } else {
        console.log("Nenhum dado de categorias encontrado");
        categoriasCanvas.parentElement.innerHTML =
          '<p style="text-align: center; color: #999; padding: 2rem;">Nenhum dado disponível</p>';
      }
    } catch (error) {
      console.error("Erro ao carregar gráfico de categorias:", error);
      categoriasCanvas.parentElement.innerHTML =
        '<p style="text-align: center; color: #999; padding: 2rem;">Erro ao carregar gráfico</p>';
    }
  }

  // Gráfico de Status das Transações (Pizza)
  const statusCanvas = document.getElementById("grafico-status");
  if (statusCanvas) {
    try {
      const statusData = JSON.parse(statusCanvas.dataset.status);
      console.log("Dados status:", statusData);

      const totalTransacoes =
        statusData.pagas + statusData.pendentes + statusData.atrasadas;

      if (totalTransacoes > 0) {
        const statusChart = new Chart(statusCanvas.getContext("2d"), {
          type: "doughnut",
          data: {
            labels: ["Pagas", "Pendentes", "Atrasadas"],
            datasets: [
              {
                data: [
                  statusData.pagas,
                  statusData.pendentes,
                  statusData.atrasadas,
                ],
                backgroundColor: [
                  "#2ecc71", // Verde para pagas
                  "#f39c12", // Laranja para pendentes
                  "#e74c3c", // Vermelho para atrasadas
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
                position: "bottom",
                labels: {
                  usePointStyle: true,
                  padding: 15,
                  font: {
                    size: 11,
                  },
                },
              },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    const total = totalTransacoes;
                    const percentage = ((context.parsed * 100) / total).toFixed(
                      1
                    );
                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                  },
                },
              },
            },
            cutout: "60%",
          },
        });
      } else {
        statusCanvas.parentElement.innerHTML =
          '<p style="text-align: center; color: #999; padding: 2rem;">Nenhuma transação encontrada</p>';
      }
    } catch (error) {
      console.error("Erro ao carregar gráfico de status:", error);
      statusCanvas.parentElement.innerHTML =
        '<p style="text-align: center; color: #999; padding: 2rem;">Erro ao carregar gráfico</p>';
    }
  }

  // Gráfico Top 5 Categorias (Barras Horizontais)
  const top5Canvas = document.getElementById("grafico-top5");
  if (top5Canvas) {
    try {
      const top5Data = JSON.parse(top5Canvas.dataset.top5);
      console.log("Dados top5:", top5Data);

      if (top5Data && top5Data.length > 0) {
        const top5Chart = new Chart(top5Canvas.getContext("2d"), {
          type: "bar",
          data: {
            labels: top5Data.map((item) => item.categoria),
            datasets: [
              {
                label: "Gastos por Categoria",
                data: top5Data.map((item) => parseFloat(item.total_gasto)),
                backgroundColor: [
                  "#3498db",
                  "#e74c3c",
                  "#f39c12",
                  "#2ecc71",
                  "#9b59b6",
                ],
                borderRadius: 6,
                borderSkipped: false,
              },
            ],
          },
          options: {
            indexAxis: "y",
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false,
              },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    return `${
                      context.label
                    }: R$ ${context.parsed.x.toLocaleString("pt-BR")}`;
                  },
                },
              },
            },
            scales: {
              x: {
                beginAtZero: true,
                ticks: {
                  callback: function (value) {
                    return "R$ " + value.toLocaleString("pt-BR");
                  },
                },
                title: {
                  display: true,
                  text: "Valor Gasto",
                },
              },
              y: {
                title: {
                  display: true,
                  text: "Categorias",
                },
              },
            },
          },
        });
      } else {
        top5Canvas.parentElement.innerHTML =
          '<p style="text-align: center; color: #999; padding: 2rem;">Nenhum dado disponível</p>';
      }
    } catch (error) {
      console.error("Erro ao carregar gráfico top 5:", error);
      top5Canvas.parentElement.innerHTML =
        '<p style="text-align: center; color: #999; padding: 2rem;">Erro ao carregar gráfico</p>';
    }
  }

  // ===== TOGGLE DE GRÁFICOS =====
  document.querySelectorAll(".toggle-grafico").forEach((button) => {
    button.addEventListener("click", function () {
      const targetId = this.dataset.target;
      const container = document.getElementById(targetId);

      if (container) {
        const isVisible = container.style.display !== "none";
        container.style.display = isVisible ? "none" : "block";

        const icon = this.querySelector("i");
        if (icon) {
          icon.className = isVisible ? "fas fa-eye-slash" : "fas fa-eye";
        }
      }
    });
  });

  // ===== FILTROS AVANÇADOS =====

  // Toggle dos filtros
  const toggleFiltros = document.getElementById("toggle-filtros");
  const filtrosContent = document.getElementById("filtros-content");

  if (toggleFiltros && filtrosContent) {
    // Inicializar como fechado
    filtrosContent.style.display = "none";

    toggleFiltros.addEventListener("click", function () {
      const isVisible = filtrosContent.style.display !== "none";

      if (isVisible) {
        filtrosContent.style.display = "none";
        this.classList.remove("rotated");
      } else {
        filtrosContent.style.display = "block";
        this.classList.add("rotated");
      }
    });
  }

  // Aplicar filtros
  const aplicarFiltros = document.getElementById("aplicar-filtros");
  const limparFiltrosBtn = document.getElementById("limpar-filtros");
  const linhasTabela = document.querySelectorAll(".tabela-despesas tbody tr");

  function filtrarTabela() {
    const categoria = document
      .getElementById("filtro-categoria")
      .value.toLowerCase();
    const valorMin =
      parseFloat(document.getElementById("filtro-valor-min").value) || 0;
    const valorMax =
      parseFloat(document.getElementById("filtro-valor-max").value) || Infinity;
    const status = document.getElementById("filtro-status").value.toLowerCase();

    let visiveis = 0;

    linhasTabela.forEach((tr) => {
      const categoriaTexto = tr.cells[2]?.textContent.toLowerCase() || "";
      const valorTexto =
        tr.cells[3]?.textContent.replace(/[R$\s\.]/g, "").replace(",", ".") ||
        "0";
      const valor = parseFloat(valorTexto) || 0;
      const statusTexto = tr.cells[5]?.textContent.toLowerCase() || "";

      let mostrar = true;

      // Filtro por categoria
      if (categoria && !categoriaTexto.includes(categoria)) {
        mostrar = false;
      }

      // Filtro por valor
      if (valorMin > 0 && valor < valorMin) {
        mostrar = false;
      }
      if (valorMax < Infinity && valor > valorMax) {
        mostrar = false;
      }

      // Filtro por status
      if (status && !statusTexto.includes(status)) {
        mostrar = false;
      }

      tr.style.display = mostrar ? "" : "none";
      if (mostrar) visiveis++;
    });

    // Atualizar contador
    console.log(`Mostrando ${visiveis} de ${linhasTabela.length} transações`);
  }

  function limparFiltros() {
    document.getElementById("filtro-categoria").value = "";
    document.getElementById("filtro-valor-min").value = "";
    document.getElementById("filtro-valor-max").value = "";
    document.getElementById("filtro-status").value = "";

    linhasTabela.forEach((tr) => {
      tr.style.display = "";
    });
  }

  if (aplicarFiltros) {
    aplicarFiltros.addEventListener("click", filtrarTabela);
  }

  if (limparFiltrosBtn) {
    limparFiltrosBtn.addEventListener("click", limparFiltros);
  }

  // ===== PAGAMENTO PARCIAL =====

  const acoesLote = document.getElementById("acoes-lote");
  const selecionadosCount = document.querySelector(".selecionados-count");
  const pagarSelecionados = document.getElementById("pagar-selecionados");
  const excluirSelecionados = document.getElementById("excluir-selecionados");
  const desmarcarTodos = document.getElementById("desmarcar-todos");
  const modalPagamentoParcial = document.getElementById(
    "modalPagamentoParcial"
  );
  const despesasSelecionadas = document.getElementById("despesas-selecionadas");
  const totalSelecionado = document.getElementById("total-selecionado");
  const quantidadeItens = document.getElementById("quantidade-itens");
  const formPagamentoParcial = document.getElementById("formPagamentoParcial");
  const contaSelectParcial = document.getElementById("id_conta_parcial");
  const saldoWarningParcial = document.getElementById("saldoWarningParcial");

  function atualizarAcoesLote() {
    const checkboxes = document.querySelectorAll(".row-select:checked");
    const count = checkboxes.length;

    if (count > 0) {
      acoesLote.style.display = "flex";
      selecionadosCount.textContent = `${count} selecionados`;
    } else {
      acoesLote.style.display = "none";
    }
  }

  // Adicionar listener para checkboxes existentes e futuros
  document.addEventListener("change", function (e) {
    if (e.target.classList.contains("row-select")) {
      atualizarAcoesLote();
    }
  });

  // Pagar selecionados
  if (pagarSelecionados) {
    pagarSelecionados.addEventListener("click", function () {
      const checkboxes = document.querySelectorAll(".row-select:checked");
      if (checkboxes.length === 0) {
        alert("Selecione pelo menos uma despesa para pagar.");
        return;
      }

      // Coletar dados das despesas selecionadas
      const despesas = [];
      let total = 0;

      checkboxes.forEach((checkbox) => {
        const tr = checkbox.closest("tr");
        const id = checkbox.value;
        const descricao = tr.cells[1]?.textContent || "";
        const valorTexto =
          tr.cells[3]?.textContent.replace(/[R$\s\.]/g, "").replace(",", ".") ||
          "0";
        const valor = parseFloat(valorTexto) || 0;
        const categoria = tr.cells[2]?.textContent || "";

        despesas.push({ id, descricao, valor, categoria });
        total += valor;
      });

      // Preencher modal
      document.getElementById("id_cartao_parcial").value =
        document.querySelector("[data-id-cartao]")?.dataset.idCartao || "";

      // Limpar e preencher lista de despesas
      despesasSelecionadas.innerHTML = "";
      despesas.forEach((despesa) => {
        const div = document.createElement("div");
        div.className = "despesa-item";
        div.innerHTML = `
          <div class="despesa-info">
            <div class="despesa-descricao">${despesa.descricao}</div>
            <div class="despesa-detalhes">${despesa.categoria}</div>
          </div>
          <div class="despesa-valor">R$ ${despesa.valor.toLocaleString(
            "pt-BR",
            { minimumFractionDigits: 2 }
          )}</div>
        `;
        despesasSelecionadas.appendChild(div);
      });

      // Atualizar totais
      totalSelecionado.textContent = `R$ ${total.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
      })}`;
      quantidadeItens.textContent = despesas.length;

      // Esconder aviso de saldo
      saldoWarningParcial.style.display = "none";

      // Mostrar modal
      modalPagamentoParcial.classList.add("show");
    });
  }

  // Verificar saldo no pagamento parcial
  if (contaSelectParcial) {
    contaSelectParcial.addEventListener("change", function () {
      const option = this.options[this.selectedIndex];
      if (option.value) {
        const saldoConta = parseFloat(option.dataset.saldo) || 0;
        const totalTexto =
          totalSelecionado.textContent
            .replace(/[R$\s\.]/g, "")
            .replace(",", ".") || "0";
        const totalPagamento = parseFloat(totalTexto) || 0;
        const saldoRestante = saldoConta - totalPagamento;

        if (saldoRestante < 0) {
          saldoWarningParcial.style.display = "block";
        } else {
          saldoWarningParcial.style.display = "none";
        }
      } else {
        saldoWarningParcial.style.display = "none";
      }
    });
  }

  // Fechar modal pagamento parcial
  document
    .getElementById("fecharModalParcial")
    ?.addEventListener("click", function () {
      modalPagamentoParcial.classList.remove("show");
    });

  document
    .getElementById("cancelarPagamentoParcial")
    ?.addEventListener("click", function () {
      modalPagamentoParcial.classList.remove("show");
    });

  // Enviar pagamento parcial
  if (formPagamentoParcial) {
    formPagamentoParcial.addEventListener("submit", async function (e) {
      e.preventDefault();

      const checkboxes = document.querySelectorAll(".row-select:checked");
      const ids = Array.from(checkboxes).map((cb) => cb.value);

      if (ids.length === 0) {
        alert("Nenhuma despesa selecionada.");
        return;
      }

      const btn = this.querySelector("button[type=submit]");
      btn.disabled = true;
      btn.querySelector(".btn-text").textContent = "Processando...";

      const formData = new FormData(this);
      formData.append("ids_despesas", JSON.stringify(ids));
      formData.append("mes", mes);
      formData.append("ano", ano);

      try {
        const res = await fetch("/despesas-cartao/pagarParcial", {
          method: "POST",
          body: formData,
        });

        const data = await res.json();

        if (!res.ok || !data.sucesso) {
          throw new Error(data.erro || "Erro ao processar pagamento parcial");
        }

        alert("✅ Pagamento parcial realizado com sucesso!");
        modalPagamentoParcial.classList.remove("show");
        location.reload();
      } catch (error) {
        alert("❌ Erro ao pagar despesas: " + error.message);
        btn.disabled = false;
        btn.querySelector(".btn-text").textContent =
          "Confirmar Pagamento Parcial";
      }
    });
  }

  // Excluir selecionados
  if (excluirSelecionados) {
    excluirSelecionados.addEventListener("click", function () {
      const checkboxes = document.querySelectorAll(".row-select:checked");
      if (checkboxes.length === 0) {
        alert("Selecione pelo menos uma despesa para excluir.");
        return;
      }

      const confirmacao = confirm(
        `Deseja excluir ${checkboxes.length} despesa(s) selecionada(s)?`
      );
      if (!confirmacao) return;

      // Aqui você pode implementar a exclusão em lote
      alert("Funcionalidade de exclusão em lote será implementada em breve.");
    });
  }

  // Desmarcar todos
  if (desmarcarTodos) {
    desmarcarTodos.addEventListener("click", function () {
      const checkboxes = document.querySelectorAll(".row-select:checked");
      checkboxes.forEach((cb) => {
        cb.checked = false;
        cb.dispatchEvent(new Event("change", { bubbles: true }));
      });
    });
  }
});
