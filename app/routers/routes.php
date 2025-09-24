<?php

require_once BASE_PATH . '/app/lib/Router.php';
require_once BASE_PATH . '/app/helpers/logger.php';
require_once BASE_PATH . '/app/helpers/AuthHelper.php';
require_once BASE_PATH . '/app/controllers/AuthController.php';
require_once BASE_PATH . '/app/controllers/CadastroController.php';
require_once BASE_PATH . '/app/controllers/CategoriaController.php';
require_once BASE_PATH . '/app/controllers/FormaTransacaoController.php';
require_once BASE_PATH . '/app/controllers/CartoesController.php';
require_once BASE_PATH . '/app/controllers/BancosController.php';
require_once BASE_PATH . '/app/controllers/DespesasController.php';
require_once BASE_PATH . '/app/controllers/MenuController.php';
require_once BASE_PATH . '/app/controllers/ReceitasController.php';
require_once BASE_PATH . '/app/controllers/FaturaController.php';


$router = new Router();
$authController = new AuthController();
$cadastroController = new CadastroController();
$categoriaController = new CategoriaController();
$formasController = new FormaTransacaoController();
$cartoesController = new CartoesController();
$bancosController = new BancosController();
$despesasController = new DespesasController();
$menuController = new MenuController();
$receitasController = new ReceitasController();
$faturaController = new FaturaController();

// ####### Rotas de navegação #######

// Rota para ir para a página de boas vindas
$router->get('/', function () {
    logEvento('Página de boas-vindas carregada');
    include BASE_PATH . '/public/views/boas-vindas.php';
});

// Rota para ir para a página de login
$router->get('/login', function () {
    logEvento('Usuário acessou a página de login');
    include BASE_PATH . '/public/views/login.php';
});

// Rota para ir para a página de cadastro
$router->get('/cadastro', function () {
    logEvento('Usuário acessou a página de cadastro');
    include BASE_PATH . '/public/views/cadastro.php';
});

// ####### Rotas de navegação - Administrativas #######

// Menu principal
$router->get('/menu', function () use ($menuController) {
    $viewData = $menuController->index();
    $view = BASE_PATH . '/app/views/menu.php';
    $pageTitle = 'Página Inicial | Fica Suave';
    include BASE_PATH . '/app/layouts/layout.php';
});


// Categorias


$router->get('/categorias', function () use ($categoriaController) {
    $viewData = $categoriaController->listar();
    $view = BASE_PATH . '/app/views/categorias.php';
    $pageTitle = 'Categorias | Fica Suave';
    include BASE_PATH . '/app/layouts/layout.php';
});



// Transações

$router->get('/formas-transacao', function () use ($formasController) {
    $viewData = $formasController->listar();
    $view = BASE_PATH . '/app/views/formas-transacao.php';
    $pageTitle = 'Formas de Transações | Fica Suave';
    include BASE_PATH . '/app/layouts/layout.php';
});

$router->post('/formas-transacao/salvar', function () use ($formasController) {
    $formasController->salvar();
});

$router->post('/formas-transacao/editar', function () use ($formasController) {
    $formasController->salvar();
});

$router->post('/formas-transacao/excluir', function () use ($formasController) {
    $formasController->excluir();
});



// Bancos

$router->get('/bancos', function () use ($bancosController) {
    $viewData = $bancosController->listar();
    $view = BASE_PATH . '/app/views/bancos.php';
    $pageTitle = 'Bancos | Fica Suave';
    include BASE_PATH . '/app/layouts/layout.php';
});

$router->post('/bancos/salvar', function () use ($bancosController) {
    $bancosController->salvar();
});

$router->post('/bancos/favoritar', function () use ($bancosController) {
    $bancosController->favoritar();
});


$router->post('/bancos/excluir', function () use ($bancosController) {
    $bancosController->excluir();
});

$router->post('/bancos/editar', function () use ($bancosController) {
    $bancosController->salvar();
});

// Despesas

$router->get('/despesas', function () use ($despesasController) {
    $viewData = $despesasController->listar();
    $view = BASE_PATH . '/app/views/despesas.php';
    $pageTitle = 'Despesas | Fica Suave';
    include BASE_PATH . '/app/layouts/layout.php';
});

// Salvar Despesa
$router->post('/despesas/salvar', function () use ($despesasController) {
    $despesasController->salvar();
});

// Atualizar Despesa
$router->post('/despesas/atualizar', function () use ($despesasController) {
    $despesasController->atualizar();
});

// Excluir Despesa
$router->post('/despesas/excluir', function () use ($despesasController) {
    $despesasController->excluir();
});

// Pagar Despesa
$router->post('/despesas/pagar', function () use ($despesasController) {
    $despesasController->pagar();
});

// Receitas

$router->get('/receitas', function () use ($receitasController) {
    $viewData = $receitasController->listar();
    $view = BASE_PATH . '/app/views/receitas.php';
    $pageTitle = 'Receitas | Fica Suave';
    include BASE_PATH . '/app/layouts/layout.php';
});

$router->post('/receitas/salvar', function () use ($receitasController) {
    $receitasController->salvar();
});

$router->post('/receitas/atualizar', function () use ($receitasController) {
    $receitasController->atualizar();
});

$router->post('/receitas/excluir', function () use ($receitasController) {
    $receitasController->excluir();
});

// Cartões

$router->get('/cartoes', function () use ($cartoesController) {
    $viewData = $cartoesController->listar();
    $view      = BASE_PATH . '/app/views/cartoes.php';
    $pageTitle = 'Cartões | Fica Suave';
    include BASE_PATH . '/app/layouts/layout.php';
});


$router->post('/cartoes/salvar', function () use ($cartoesController) {
    $cartoesController->salvar();
});

$router->post('/cartoes/excluir', function () use ($cartoesController) {
    $cartoesController->excluir();
});


// Despesas de Fatura

// Fatura do Cartão de Crédito
$router->get('/cartoes/fatura/(\d+)', function ($idCartao) use ($faturaController) {
    $viewData = $faturaController->listarPorCartao($idCartao);
    $view = BASE_PATH . '/app/views/despesa-cartao-credito.php';
    $pageTitle = 'Fatura do Cartão | Fica Suave';
    include BASE_PATH . '/app/layouts/layout.php';
});

// Salvar Despesa Fatura
$router->post('/despesas-cartao/salvar-fatura', function () use ($faturaController) {
    $faturaController->salvar();
});

// Excluir Despesa Fatura
$router->post('/despesas-cartao/excluir-fatura', function () use ($faturaController) {
    $faturaController->excluir();
});

// Atualizar Despesa Fatura
$router->post('/despesas-cartao/atualizar-fatura', function () use ($faturaController) {
    $faturaController->atualizar();
});

// Pagar Fatura Completa
$router->post('/despesas-cartao/pagarFatura', function () use ($faturaController) {
    $faturaController->pagarFatura();
});



// ####### Rotas de criação de cadastros #######

// Rota para criar cadastro
$router->post('/cadastro/salvar', function () use ($cadastroController) {
    $cadastroController->registrar();
});

// ####### Rotas de autenticação #######

// Autenticar login (POST do formulário JS)
$router->post('/admin/authenticate', function () use ($authController) {
    $authController->login();
    logEvento("🔐 Rota POST /admin/authenticate foi chamada!");
});

// Logout do sistema
$router->get('/logout', function () use ($authController) {
    $authController->logout();
});

// Excluir ou desativar categoria
$router->post('/categorias/excluir', function () use ($categoriaController) {
    $categoriaController->excluir();
});
