document.getElementById("login-form").addEventListener("submit", function (e) {
  e.preventDefault(); // Impede envio padrão

  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;

  const formData = new FormData();
  formData.append("email", email);
  formData.append("password", password);

  fetch("/admin/authenticate", {
    method: "POST",
    body: formData,
    credentials: "same-origin", // Garante que cookies (sessão) sejam mantidos
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        window.location.href = "/menu";
      } else {
        const erroDiv = document.getElementById("login-error");
        erroDiv.textContent = data.error || "E-mail ou senha incorretos.";
        erroDiv.style.display = "block";
      }
    })
    .catch((error) => {
      console.error("Erro na requisição:", error);
      const erroDiv = document.getElementById("login-error");
      erroDiv.textContent = "Erro ao fazer login. Tente novamente.";
      erroDiv.style.display = "block";
    });
});
