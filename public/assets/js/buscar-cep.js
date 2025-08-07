document
  .getElementById("buscarEndereco")
  .addEventListener("click", async () => {
    const cep = document.getElementById("cep").value.trim().replace("-", "");

    if (cep.length !== 8 || isNaN(cep)) {
      alert("CEP inválido. Digite um CEP com 8 números.");
      return;
    }

    try {
      const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
      const data = await response.json();

      if (data.erro) {
        alert("CEP não encontrado. Tenta outro aí!");
        return;
      }

      document.getElementById("rua").value = data.logradouro || "";
      document.getElementById("cidade").value = data.localidade || "";
      document.getElementById("estado").value = data.uf || "";
      document.getElementById("uf").value = data.uf || "";
      document.getElementById("pais").value = "Brasil";
    } catch (erro) {
      alert("Erro ao buscar endereço. Tenta novamente.");
      console.error("Erro no fetch do CEP:", erro);
    }
  });
