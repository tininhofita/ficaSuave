// Função para retornar a classe do ícone da bandeira com base no nome
function getIconeBandeira(bandeira) {
  const mapa = {
    Visa: "fa-brands fa-cc-visa",
    MasterCard: "fa-brands fa-cc-mastercard",
    HiperCard: "fa-solid fa-credit-card",
    "American Express": "fa-brands fa-cc-amex",
    SoroCard: "fa-solid fa-id-card",
    BNDES: "fa-solid fa-building-columns",
  };
  return mapa[bandeira] || "fa-solid fa-credit-card";
}

// Elementos do botão customizado de bandeira
const selectToggle = document.querySelector(".select-toggle");
const selectOptions = document.querySelector(".select-options");
const inputBandeira = document.getElementById("bandeira");

// Exibe ou oculta o dropdown de opções ao clicar no botão
selectToggle.addEventListener("click", () => {
  selectOptions.style.display =
    selectOptions.style.display === "block" ? "none" : "block";
});

// Define o valor da bandeira ao clicar em uma das opções
document.querySelectorAll(".select-options li").forEach((item) => {
  item.addEventListener("click", () => {
    const nome = item.dataset.bandeira;
    const icone = getIconeBandeira(nome);

    selectToggle.innerHTML = `<i class="${icone}"></i> ${nome}`;
    inputBandeira.value = nome;
    selectOptions.style.display = "none";
  });
});

// Fecha o dropdown ao clicar fora dele
document.addEventListener("click", function (e) {
  if (!document.getElementById("bandeira-select").contains(e.target)) {
    selectOptions.style.display = "none";
  }
});

// Mapa de cores para hexadecimal
const coresMap = {
  purple: "#8b5cf6",
  blue: "#3b82f6",
  green: "#10b981",
  red: "#ef4444",
  orange: "#f97316",
  pink: "#ec4899",
  indigo: "#6366f1",
  teal: "#14b8a6",
  gray: "#6b7280",
  yellow: "#eab308",
};

// Função para aplicar cor ao cartão (apenas preview no modal)
function aplicarCorCartao(cor) {
  // Aplica a cor apenas aos cartões que estão sendo editados (preview)
  const cartaoEmEdicao = document.querySelector(
    '.card-cartao[data-editando="true"]'
  );
  if (cartaoEmEdicao) {
    cartaoEmEdicao.setAttribute("data-cor", coresMap[cor]);
    cartaoEmEdicao.style.background = coresMap[cor];
  }
}

// Função para aplicar cores baseadas no hexadecimal do banco
function aplicarCoresDoBanco() {
  const cartoes = document.querySelectorAll(".card-cartao");
  cartoes.forEach((cartao) => {
    const corHex = cartao.getAttribute("data-cor");
    if (corHex && corHex.startsWith("#")) {
      cartao.style.background = corHex;
    }
  });
}

// Inicializa o seletor de cores quando o DOM estiver carregado
document.addEventListener("DOMContentLoaded", () => {
  // Aplica cores do banco de dados
  aplicarCoresDoBanco();

  const colorOptions = document.querySelectorAll(".color-option");
  const corCartaoInput = document.getElementById("cor_cartao");

  // Seleciona uma cor
  colorOptions.forEach((option) => {
    option.addEventListener("click", () => {
      // Remove a classe selected de todas as opções
      colorOptions.forEach((opt) => opt.classList.remove("selected"));

      // Adiciona a classe selected na opção clicada
      option.classList.add("selected");

      // Define o valor hexadecimal no input hidden
      const corHex = coresMap[option.dataset.color];
      corCartaoInput.value = corHex;

      // Aplica a cor aos cartões para preview
      aplicarCorCartao(option.dataset.color);
    });
  });

  // Seleciona a cor padrão (azul) ao carregar
  const blueOption = document.querySelector('[data-color="blue"]');
  if (blueOption) {
    blueOption.classList.add("selected");
  }
});

// Abrir modal para novo cartão
document
  .getElementById("btn-adicionar-cartao")
  .addEventListener("click", () => {
    document.getElementById("form-cartao").reset();
    document.getElementById("id_cartao").value = "";
    selectToggle.innerHTML = "Selecione a bandeira";

    // Reset da cor para azul (padrão)
    document.getElementById("cor_cartao").value = "#3b82f6";
    const colorOptions = document.querySelectorAll(".color-option");
    colorOptions.forEach((opt) => opt.classList.remove("selected"));
    const blueOption = document.querySelector('[data-color="blue"]');
    if (blueOption) {
      blueOption.classList.add("selected");
    }

    // Remove flag de edição
    document.querySelectorAll(".card-cartao").forEach((cartao) => {
      cartao.removeAttribute("data-editando");
    });

    // Atualiza título para novo cartão
    document.getElementById("titulo-modal-cartao").textContent = "Novo Cartão";
    document.querySelector(".modal-subtitle").textContent =
      "Configure os dados do cartão de crédito";

    document.getElementById("modal-cartao").classList.add("exibir-modal");
  });

// Fechar modal
// Botão de fechar no header
document.getElementById("fecharModalCartao").addEventListener("click", () => {
  document.getElementById("modal-cartao").classList.remove("exibir-modal");
});

// Botão cancelar no footer
document.getElementById("cancelarModalCartao").addEventListener("click", () => {
  document.getElementById("modal-cartao").classList.remove("exibir-modal");
});

// Fechar clicando fora do modal
document.getElementById("modal-cartao").addEventListener("click", (e) => {
  if (!e.target.closest(".modal-conteudo")) {
    document.getElementById("modal-cartao").classList.remove("exibir-modal");
  }
});

// Submeter o formulário (novo ou edição)
document.getElementById("form-cartao").addEventListener("submit", function (e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const rota = "/cartoes/salvar";

  fetch(rota, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (response.redirected) {
        window.location.href = response.url;
      } else {
        // Se não redirecionou, recarregar a página
        window.location.reload();
      }
    })
    .catch((error) => {
      console.error("Erro ao salvar:", error);
      alert("Erro ao salvar cartão. Tente novamente.");
    });
});

// Preencher os dados ao clicar em "Editar"
document.querySelectorAll(".btn-editar").forEach((botao) => {
  botao.addEventListener("click", () => {
    if (botao.dataset.padrao === "1") {
      alert("Item padrão do sistema. Não pode ser editado.");
      return;
    }

    // Preenche os campos com os dados existentes
    document.getElementById("id_cartao").value = botao.dataset.id;
    document.getElementById("limite").value = botao.dataset.limite;
    document.getElementById("nome_cartao").value = botao.dataset.nome;
    document.getElementById("bandeira").value = botao.dataset.bandeira || "";
    document.getElementById("id_conta").value = botao.dataset.conta;
    document.getElementById("dia_fechamento").value =
      botao.dataset.dia_fechamento;
    document.getElementById("vencimento_fatura").value =
      botao.dataset.vencimento_fatura;

    // Define a cor do cartão
    const corCartao = botao.dataset.cor || "#3b82f6";
    document.getElementById("cor_cartao").value = corCartao;

    // Remove flag de edição de todos os cartões primeiro
    document.querySelectorAll(".card-cartao").forEach((cartao) => {
      cartao.removeAttribute("data-editando");
    });

    // Marca o cartão sendo editado para preview
    const cartaoElement = document.querySelector(
      `a[href*="/cartoes/fatura/${botao.dataset.id}"]`
    );
    if (cartaoElement) {
      cartaoElement.setAttribute("data-editando", "true");
    }

    // Encontra a cor correspondente no seletor
    const corCorrespondente =
      Object.keys(coresMap).find((key) => coresMap[key] === corCartao) ||
      "blue";

    // Atualiza a seleção visual da cor
    const colorOptions = document.querySelectorAll(".color-option");
    colorOptions.forEach((opt) => opt.classList.remove("selected"));
    const corSelecionada = document.querySelector(
      `[data-color="${corCorrespondente}"]`
    );
    if (corSelecionada) {
      corSelecionada.classList.add("selected");
    }

    // Atualiza o botão visual com ícone da bandeira
    const bandeira = botao.dataset.bandeira;
    const icone = getIconeBandeira(bandeira);
    selectToggle.innerHTML = `<i class="${icone}"></i> ${bandeira}`;

    // Atualiza título para edição
    document.getElementById("titulo-modal-cartao").textContent =
      "Editar Cartão";
    document.querySelector(".modal-subtitle").textContent =
      "Atualize os dados do cartão de crédito";

    // Exibe o modal
    document.getElementById("modal-cartao").classList.add("exibir-modal");
  });
});

// Excluir cartão ao clicar no botão "Excluir"
document.querySelectorAll(".btn-excluir").forEach((botao) => {
  botao.addEventListener("click", () => {
    if (botao.dataset.padrao === "1") {
      alert("Item padrão do sistema. Não pode ser excluído.");
      return;
    }

    if (confirm("Tem certeza que deseja excluir?")) {
      fetch("/cartoes/excluir", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${botao.dataset.id}`,
      }).then((response) => {
        if (response.redirected) {
          window.location.href = response.url;
        }
      });
    }
  });
});

// Máscara de moeda no campo limite
const campoLimiteInput = document.getElementById("limite");

campoLimiteInput.addEventListener("input", (e) => {
  let value = e.target.value.replace(/\D/g, "");
  value = (parseInt(value, 10) / 100).toFixed(2);
  e.target.value = value
    .toString()
    .replace(".", ",")
    .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
});
