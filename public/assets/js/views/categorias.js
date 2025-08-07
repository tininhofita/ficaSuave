document.addEventListener("DOMContentLoaded", () => {
  // Confirmação ao excluir
  const forms = document.querySelectorAll(".form-excluir");
  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      if (
        !confirm("Tem certeza que deseja excluir ou desativar esta categoria?")
      ) {
        e.preventDefault();
      }
    });
  });

  // Toggle de subcategorias
  const toggleBtns = document.querySelectorAll(".toggle-subcategorias");

  toggleBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      const subcats = document.querySelectorAll(`.subcat-${id}`);

      subcats.forEach((row) => {
        row.style.display =
          row.style.display === "table-row" ? "none" : "table-row";
      });

      // Alterna a setinha
      const icon = btn.querySelector("i");
      icon.classList.toggle("ph-caret-right");
      icon.classList.toggle("ph-caret-down");
    });
  });
});
