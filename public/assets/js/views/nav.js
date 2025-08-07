document.addEventListener("DOMContentLoaded", () => {
  const menuItems = document.querySelectorAll(".has-submenu > a");
  const sideMenu = document.querySelector(".side-menu");
  const mainContent = document.querySelector("main");

  // Menu começa reduzido
  sideMenu.classList.add("reduced");
  mainContent.classList.add("reduced");

  // Expande o menu ao passar o mouse
  sideMenu.addEventListener("mouseenter", function () {
    sideMenu.classList.remove("reduced");
    mainContent.classList.remove("reduced");
  });

  // Reduz o menu ao sair do hover
  sideMenu.addEventListener("mouseleave", function () {
    sideMenu.classList.add("reduced");
    mainContent.classList.add("reduced");

    // Fecha todos os submenus quando o menu é recolhido
    document.querySelectorAll(".submenu.show").forEach((submenu) => {
      submenu.classList.remove("show");
    });
  });

  // Abre/fecha submenus ao clicar
  menuItems.forEach((item) => {
    item.addEventListener("click", function (e) {
      e.preventDefault();
      const submenu = this.nextElementSibling;

      // Fecha todos os outros submenus antes de abrir o atual
      document.querySelectorAll(".submenu.show").forEach((openSubmenu) => {
        if (openSubmenu !== submenu) {
          openSubmenu.classList.remove("show");
        }
      });

      submenu.classList.toggle("show");
    });
  });

  // Fecha o submenu ao clicar fora do menu
  document.addEventListener("click", function (e) {
    if (!sideMenu.contains(e.target)) {
      document.querySelectorAll(".submenu.show").forEach((submenu) => {
        submenu.classList.remove("show");
      });
    }
  });
});
