document.addEventListener("DOMContentLoaded", () => {
  const modalExcluir = document.getElementById("modal-excluir-receita");
  const descricaoSpan = modalExcluir.querySelector(
    "#excluir-descricao-receita"
  );
  const valorSpan = modalExcluir.querySelector("#excluir-valor-receita");
  const avisoParcelado = modalExcluir.querySelector(
    "#aviso-exclusao-parcelada-receita"
  );
  const textoParcelado = modalExcluir.querySelector(
    "#texto-exclusao-parcelada-receita"
  );
  const btnCancelarEx = modalExcluir.querySelector(
    "#cancelar-exclusao-receita"
  );
  const btnConfirmarEx = modalExcluir.querySelector(
    "#confirmar-exclusao-receita"
  );

  // 1) Abre o modal e preenche descrição/valor
  document.querySelectorAll(".btn-excluir-receita").forEach((btn) => {
    btn.addEventListener("click", () => {
      console.log("dataset do btn:", btn.dataset);

      descricaoSpan.textContent = btn.dataset.descricao;
      valorSpan.textContent = btn.dataset.valor;

      // Parcelas?
      if (btn.dataset.parcelado === "1" && +btn.dataset.totalParcelas > 1) {
        textoParcelado.textContent = `Esta receita está na parcela ${btn.dataset.numeroParcelas} de ${btn.dataset.totalParcelas}.`;
        avisoParcelado.style.display = "block";
      } else {
        avisoParcelado.style.display = "none";
      }

      btnConfirmarEx.dataset.id = btn.dataset.id;
      modalExcluir.classList.add("exibir-modal");
    });
  });

  // 2) Fecha ao clicar no cancelar
  btnCancelarEx.addEventListener("click", () => {
    modalExcluir.classList.remove("exibir-modal");
  });

  // 3) Fecha ao clicar fora do conteúdo
  modalExcluir.addEventListener("click", (e) => {
    if (!e.target.closest(".modal-conteudo")) {
      modalExcluir.classList.remove("exibir-modal");
    }
  });

  // 4) Dispara o POST de exclusão
  btnConfirmarEx.addEventListener("click", async () => {
    const id = btnConfirmarEx.dataset.id;
    const escopo =
      document.querySelector("input[name=escopo_exclusao]:checked")?.value ||
      "somente";

    try {
      const resp = await fetch("/receitas/excluir", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${encodeURIComponent(id)}&escopo=${encodeURIComponent(
          escopo
        )}`,
      });
      const json = await resp.json();
      if (json.sucesso) {
        document
          .querySelector(`.btn-excluir-receita[data-id="${id}"]`)
          .closest("tr")
          .remove();
      } else {
        alert("Falha ao excluir: " + (json.erro || "erro desconhecido"));
      }
    } catch (err) {
      console.error(err);
      alert("Erro na requisição: " + err.message);
    } finally {
      modalExcluir.classList.remove("exibir-modal");
    }
  });
});
