document
  .getElementById("cadastro-form")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const form = e.target;
    const senha = form.senha.value.trim();
    const confirmarSenha = form.confirmarSenha.value.trim();

    if (senha !== confirmarSenha) {
      alert("As senhas não coincidem, campeão!");
      return;
    }

    const formData = new FormData(form);

    try {
      const response = await fetch("/cadastro/salvar", {
        method: "POST",
        body: formData,
      });

      const resultado = await response.json();

      if (resultado.success) {
        alert("Cadastro realizado com sucesso! 🥳 Agora é só fazer o login.");
        window.location.href = "/login";
      } else {
        alert("Erro: " + resultado.error);
      }
    } catch (erro) {
      alert("Erro inesperado! Tenta de novo, beleza?");
      console.error("Erro ao cadastrar:", erro);
    }
  });
