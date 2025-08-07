// Abrir modal para nova forma
document.getElementById("btn-adicionar-forma").addEventListener("click", () => {
  document.getElementById("form-forma-transacao").reset();
  document.getElementById("id_forma_transacao").value = "";
  document.getElementById("modal-forma").classList.add("exibir-modal");
});

// Cancelar
document.querySelector(".btn-cancelar").addEventListener("click", () => {
  document.getElementById("modal-forma").classList.remove("exibir-modal");
});

// Submeter formulário (novo ou edição)
document
  .getElementById("form-forma-transacao")
  .addEventListener("submit", function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    const id = formData.get("id_forma_transacao");
    const rota = id ? "/formas-transacao/editar" : "/formas-transacao/salvar";

    fetch(rota, {
      method: "POST",
      body: formData,
    }).then((response) => {
      if (response.redirected) {
        window.location.href = response.url;
      }
    });
  });

// Clicar em editar
document.querySelectorAll(".btn-editar").forEach((btn) => {
  btn.addEventListener("click", () => {
    if (btn.dataset.padrao === "1") {
      alert("Item padrão do sistema. Não pode ser editado.");
      return;
    }

    document.getElementById("id_forma_transacao").value = btn.dataset.id;
    document.getElementById("nome").value = btn.dataset.nome;
    document.getElementById("tipo").value = btn.dataset.tipo;
    document.getElementById("uso").value = btn.dataset.uso;
    document.getElementById("ativa").checked = btn.dataset.ativa == "1";

    document.getElementById("modal-forma").classList.add("exibir-modal");
  });
});

// Excluir
document.querySelectorAll(".btn-excluir").forEach((btn) => {
  btn.addEventListener("click", () => {
    if (btn.dataset.padrao === "1") {
      alert("Item padrão do sistema. Não pode ser excluído.");
      return;
    }

    if (confirm("Tem certeza que deseja excluir?")) {
      fetch("/formas-transacao/excluir", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${btn.dataset.id}`,
      }).then((response) => {
        if (response.redirected) {
          window.location.href = response.url;
        }
      });
    }
  });
});
