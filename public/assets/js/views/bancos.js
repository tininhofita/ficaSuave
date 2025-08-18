document.addEventListener("DOMContentLoaded", () => {
  // ========= ELEMENTOS =========
  const modal = document.getElementById("modal-cadastro-banco");
  const form = document.getElementById("form-cadastro-banco");
  const btnNovo = document.getElementById("btn-adicionar-banco");
  const btnCancel = document.querySelector(".btn-cancelar");
  const inputId = document.getElementById("id_conta");
  const inputNome = document.getElementById("nome_conta");
  const selectTipo = document.getElementById("tipo");
  const inputBanco = document.getElementById("banco");
  const checkAtiva = document.getElementById("ativa");
  const h3Titulo = document.getElementById("titulo-modal-banco");
  const saldoInput = document.getElementById("saldo_inicial");

  // ========= FUNÇÕES AUX =========
  function abrirModal() {
    modal.classList.add("exibir-modal");
  }
  function fecharModal() {
    modal.classList.remove("exibir-modal");
  }
  function resetFormNovo() {
    form.reset();
    inputId.value = "";
    h3Titulo.textContent = "Cadastro de Conta Bancária";
    // zera máscara do saldo
    if (saldoInput) {
      saldoInput.value = "0,00";
      saldoInput.setAttribute("data-raw", "0.00");
    }
  }
  function preencherFormEdicao(btn) {
    inputId.value = btn.dataset.id || "";
    inputNome.value = btn.dataset.nome || "";
    selectTipo.value = btn.dataset.tipo || "corrente";
    inputBanco.value = btn.dataset.banco || "";
    checkAtiva.checked = btn.dataset.ativa === "1";

    // saldo com máscara (se veio no dataset)
    if (saldoInput) {
      const valorMascarado = btn.dataset.saldoInicial || "0,00";
      saldoInput.value = valorMascarado;
      const raw = (valorMascarado || "0,00")
        .replace(/\./g, "")
        .replace(",", ".");
      const num = parseFloat(raw) || 0;
      saldoInput.setAttribute("data-raw", num.toFixed(2));
    }
    h3Titulo.textContent = "Editar Conta Bancária";
  }

  // ========= MÁSCARA BRL =========
  if (saldoInput) {
    saldoInput.addEventListener("input", (e) => {
      let v = e.target.value.replace(/\D/g, "");
      if (!v) v = "0";
      v = (parseInt(v, 10) / 100).toFixed(2); // 123456 -> "1234.56"
      const [int, dec] = v.split(".");
      const intFmt = int.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      e.target.value = `${intFmt},${dec}`; // "1.234,56"
      e.target.setAttribute("data-raw", v); // guarda "1234.56"
    });
    saldoInput.addEventListener("focus", () => {
      if (!saldoInput.value.trim()) {
        saldoInput.value = "0,00";
        saldoInput.setAttribute("data-raw", "0.00");
      }
    });
  }

  // ========= AÇÕES =========
  btnNovo?.addEventListener("click", () => {
    resetFormNovo();
    abrirModal();
  });

  btnCancel?.addEventListener("click", () => fecharModal());

  modal?.addEventListener("click", (e) => {
    if (!e.target.closest(".modal-content")) fecharModal();
  });

  // Submit (novo/editar) — sempre normaliza saldo_inicial
  form?.addEventListener("submit", (e) => {
    e.preventDefault();

    // normaliza saldo_inicial
    let raw = saldoInput?.getAttribute("data-raw");
    if (!raw) {
      const txt = (saldoInput?.value || "0")
        .replace(/\./g, "")
        .replace(",", ".");
      const num = parseFloat(txt) || 0;
      raw = num.toFixed(2);
      saldoInput?.setAttribute("data-raw", raw);
    }

    const fd = new FormData(form);
    fd.set("saldo_inicial", raw); // garante número com ponto

    const id = fd.get("id_conta");
    const rota = id ? "/bancos/editar" : "/bancos/salvar";

    fetch(rota, { method: "POST", body: fd })
      .then((res) => {
        if (res.redirected) window.location.href = res.url;
      })
      .catch((err) => {
        console.error(err);
        alert("Erro ao salvar conta.");
      });
  });

  // Favoritar (apenas uma por usuário)
  document.querySelectorAll(".btn-favorita").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const id = btn.dataset.id;
      try {
        const res = await fetch("/bancos/favoritar", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id=${encodeURIComponent(id)}`,
        });

        let data;
        try {
          data = await res.json();
        } catch {
          const txt = await res.text();
          console.error("Resposta não-JSON:", txt);
          alert("Falha ao definir favorita (resposta inesperada).");
          return;
        }

        if (!data?.sucesso) {
          alert(data?.erro || "Falha ao definir/desfazer favorita");
          return;
        }

        window.location.reload();
      } catch (e) {
        console.error(e);
        alert("Erro de rede ao definir favorita.");
      }
    });
  });

  // Editar
  document.querySelectorAll(".btn-editar").forEach((btn) => {
    btn.addEventListener("click", () => {
      resetFormNovo(); // garante estado limpo
      preencherFormEdicao(btn); // carrega dados do dataset
      abrirModal();
    });
  });

  // Excluir
  document.querySelectorAll(".btn-excluir").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (!confirm("Tem certeza que deseja excluir esta conta bancária?"))
        return;
      fetch("/bancos/excluir", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${encodeURIComponent(btn.dataset.id)}`,
      })
        .then((res) => {
          if (res.redirected) window.location.href = res.url;
        })
        .catch((err) => {
          console.error(err);
          alert("Erro ao excluir conta.");
        });
    });
  });
});
