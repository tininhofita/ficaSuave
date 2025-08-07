// Abrir modal de novo banco
document.getElementById("btn-adicionar-banco").addEventListener("click", () => {
  document.getElementById("form-cadastro-banco").reset();
  document.getElementById("id_conta").value = "";
  document.getElementById("titulo-modal-banco").textContent =
    "Cadastro de Conta Bancária";
  document.getElementById("modal-cadastro-banco").classList.add("exibir-modal");
});

// Cancelar e fechar modal
document.querySelector(".btn-cancelar").addEventListener("click", () => {
  document
    .getElementById("modal-cadastro-banco")
    .classList.remove("exibir-modal");
});

// Submeter form (novo ou edição)
document
  .getElementById("form-cadastro-banco")
  .addEventListener("submit", function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const id = formData.get("id_conta");

    const rota = id ? "/bancos/editar" : "/bancos/salvar";
    fetch(rota, {
      method: "POST",
      body: formData,
    }).then((res) => {
      if (res.redirected) {
        window.location.href = res.url;
      }
    });
  });

// Botão editar
document.querySelectorAll(".btn-editar").forEach((botao) => {
  botao.addEventListener("click", () => {
    document.getElementById("id_conta").value = botao.dataset.id;
    document.getElementById("nome_conta").value = botao.dataset.nome;
    document.getElementById("tipo").value = botao.dataset.tipo;
    document.getElementById("banco").value = botao.dataset.banco;
    document.getElementById("ativa").checked = botao.dataset.ativa === "1";
    document.getElementById("titulo-modal-banco").textContent =
      "Editar Conta Bancária";

    document
      .getElementById("modal-cadastro-banco")
      .classList.add("exibir-modal");
  });
});

// Botão excluir
document.querySelectorAll(".btn-excluir").forEach((botao) => {
  botao.addEventListener("click", () => {
    if (confirm("Tem certeza que deseja excluir esta conta bancária?")) {
      fetch("/bancos/excluir", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${botao.dataset.id}`,
      }).then((res) => {
        if (res.redirected) {
          window.location.href = res.url;
        }
      });
    }
  });
});
