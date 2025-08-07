<?php
ob_start();
require_once BASE_PATH . '/app/helpers/AuthHelper.php';
require_once BASE_PATH . '/app/config/config.php';
verificarLogin();

$usuarioId = usuarioLogado()['id_usuario'];

// pega saldo da conta favorita
require_once BASE_PATH . '/app/models/MenuModel.php';
$menuModel     = new MenuModel();
$saldoFavorita = $menuModel->getSaldoContaFavorita($usuarioId);

// == DESPESAS ==
require_once BASE_PATH . '/app/models/DespesasModel.php';
$despModel     = new DespesasModel();
$categorias    = $despModel->buscarCategorias($usuarioId);
$subcategorias = $despModel->buscarSubcategorias($usuarioId);
$formas        = $despModel->buscarFormasTransacao($usuarioId);
$contas        = $despModel->buscarContas($usuarioId);

// Cartões (se você tiver CartoesModel)
require_once BASE_PATH . '/app/models/CartoesModel.php';
$cartModel = new CartoesModel();
$cartoes   = $cartModel->buscarTodas($usuarioId);

// == RECEITAS ==
require_once BASE_PATH . '/app/models/ReceitasModel.php';
$recModel            = new ReceitasModel();
$categoriasReceitas  = $recModel->buscarCategoriasReceita($usuarioId);
$subcategoriasReceita = $recModel->buscarSubcategoriasReceita($usuarioId);
$formasReceita       = $recModel->buscarFormasTransacaoReceita($usuarioId);



// Cache control
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Fica Suave' ?></title>
    <!-- Estilos principais  -->
    <link rel="stylesheet" href="/assets/css/layouts/layout.css">
    <!-- Estilos de terceiros -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Ícones -->
    <link rel="icon" type="image/x-icon" href="/assets/img/fav_tininho.ico">
</head>

<body>

    <header class="header">
        <div class="container-header">
            <div class="account-info">
                <a href="/menu"><img src="/assets/img/escrita3.png" alt="Mascote Fica Suave"></a>
                <span class="saldo">Saldo: R$ <?= number_format($saldoFavorita, 2, ',', '.') ?></span>
                <span class="user-name">
                    <?= htmlspecialchars($_SESSION['nome_usuario'] ?? 'Usuário') ?>
                </span>
            </div>
            <div class="cadastro-rapido">
                <!-- Botão para receita -->
                <div>
                    <button type="button" id="btn-adicionar-receita" class="btn-padrao btn-receita">+ Nova Receita</button>
                </div>
                <!-- Botão para despesa normal -->
                <div>
                    <button type="button" class="btn-padrao btn-nova-despesa btn-abrir-despesa" data-tipo="normal">
                        + Despesa
                    </button>
                </div>

                <!-- Botão para despesa com cartão -->
                <div>
                    <button type="button" class="btn-padrao btn-nova-despesa-cartao btn-abrir-despesa" data-tipo="cartao">
                        + Despesa Cartão
                    </button>
                </div>

            </div>
            <div>
                <a href="/logout" class="btn-padrao logout-btn">Logout</a>
            </div>
        </div>
    </header>


    <nav class="side-menu" aria-label="Menu de navegação lateral">
        <h2 class="menu-title">Fica Suave</h2>
        <ul class="menu-list">
            <li><a href="/menu"><i class="fas fa-home"></i> <span>Início</span></a></li>

            <!-- <li class="has-submenu">
            <a href="javascript:void(0);">
                <i class="fas fa-wallet"></i> <span>Transações</span>
                <i class="fa-solid fa-angle-down submenu-icon"></i>
            </a>
            <ul class="submenu">
                <li><a href="/despesas"><i class="fas fa-arrow-circle-down"></i> Despesas</a></li>
                <li><a href="/receitas"><i class="fas fa-arrow-circle-up"></i> Receitas</a></li>
                <li><a href="/transferencias"><i class="fas fa-exchange-alt"></i> Transferências</a></li>
            </ul>
        </li> -->

            <li><a href="/despesas"><i class="fas fa-arrow-circle-down"></i> <span>Despesas</span></a></li>

            <li><a href="/receitas"><i class="fas fa-arrow-circle-up"></i> <span>Receitas</span></a></li>

            <li><a href="/cartoes"><i class="fa-solid fa-credit-card"></i><span>Cartões</span></a></li>

            <li><a href="/categorias"><i class="fas fa-tags"></i> <span>Categorias</span></a></li>

            <li><a href="/bancos"><i class="ph ph-bank"></i><span>Contas</span></a></li>

            <li><a href="/planejamento"><i class="fas fa-bullseye"></i> <span>Planejamento</span></a></li>

            <li><a href="/relatorios"><i class="fas fa-chart-pie"></i> <span>Relatórios</span></a></li>

            <li><a href="/metas"><i class="fas fa-flag-checkered"></i> <span>Metas Financeiras</span></a></li>

            <li><a href="/resumo"><i class="fas fa-clipboard-list"></i> <span>Resumo Mensal</span></a></li>

            <li class="has-submenu">
                <a href="javascript:void(0);">
                    <i class="fas fa-cog"></i> <span>Configurações</span>
                    <i class="fa-solid fa-angle-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li><a href="/bancos"><i class="ph ph-bank"></i>Contas</a></li>
                    <li><a href="/cartoes"><i class="ph ph-credit-card"></i>Cartões</a></li>
                    <li><a href="/formas-transacao"><i class="ph ph-currency-dollar-simple"></i>Formas de Transações</a></li>
                </ul>
            </li>


        </ul>
    </nav>

    <main class="conteudo-principal">
        <?php
        // injeta as variáveis vindas do controller 
        extract($viewData);
        ?>
        <?php include $view; ?>

        <!-- Modal de despesas (normais e cartao)-->
        <?php include BASE_PATH . '/app/views/componentes/modal-despesas.php'; ?>
        <!-- Modal para pagar despesas normais-->
        <?php include BASE_PATH . '/app/views/componentes/modal-pagamento.php'; ?>
        <!-- Modal para excluir despesas normais-->
        <?php include BASE_PATH . '/app/views/componentes/modal-excluir.php'; ?>
        <!-- Modal de receitas -->
        <?php include BASE_PATH . '/app/views/componentes/modal-receitas.php'; ?>
        <!-- Modal de excluir receitas -->
        <?php include BASE_PATH . '/app/views/componentes/modal-excluir-receita.php'; ?>

    </main>

    <footer>
        <p>&copy; 2025 Fica Suave. Todos os direitos reservados.</p>
        <p>
            Desenvolvido com
            <i class="ph ph-heart"></i>,
            <i class="ph ph-coffee"></i>
            e muita visão por
            <a href="#" target="_blank"> Tininho Fita</a>
        </p>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="/assets/js/views/nav.js"></script>
    <!-- Script do modal de despesas normais (incluir, excluir, editar) -->
    <script src="/assets/js/views/componentes/despesas-unificadas.js"></script>
    <!-- Script do modal de receitas -->
    <script src="/assets/js/views/componentes/modal-receitas.js"></script>



</body>

</html>

<?php ob_end_flush(); ?>