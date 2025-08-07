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

// Abrir modal para novo cartão
document
  .getElementById("btn-adicionar-cartao")
  .addEventListener("click", () => {
    document.getElementById("form-cartao").reset();
    document.getElementById("id_cartao").value = "";
    selectToggle.innerHTML = "Selecione a bandeira";
    document.getElementById("modal-cartao").classList.add("exibir-modal");
  });

// Fechar modal (cancelar)
document.querySelector(".btn-cancelar").addEventListener("click", () => {
  document.getElementById("modal-cartao").classList.remove("exibir-modal");
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
  }).then((response) => {
    if (response.redirected) {
      window.location.href = response.url;
    }
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

    // Atualiza o botão visual com ícone da bandeira
    const bandeira = botao.dataset.bandeira;
    const icone = getIconeBandeira(bandeira);
    selectToggle.innerHTML = `<i class="${icone}"></i> ${bandeira}`;

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
