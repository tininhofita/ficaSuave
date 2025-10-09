<?php
require_once BASE_PATH . '/app/models/MenuModel.php';

class MenuController
{
    private $model;

    public function __construct()
    {
        $this->model = new MenuModel();
    }

    public function index()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        verificarLogin();
        $usuario = usuarioLogado();
        if (!$usuario) {
            header('Location: /login');
            exit();
        }
        $idUsuario = $usuario['id_usuario'];

        // Obtém parâmetros de mês e ano da URL ou usa valores padrão
        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
        $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

        $totalCartao = $this->model->somarDespesasCartao($idUsuario, $mes, $ano);
        $totalPendentes = $this->model->somarDespesasPendentes($idUsuario, $mes, $ano);
        $totalPagos = $this->model->somarDespesasPagas($idUsuario, $mes, $ano);
        $gastosCartoes = $this->model->listarGastosPorCartao($idUsuario, $mes, $ano);
        $gastosCategorias = $this->model->listarGastosPorCategoria($idUsuario, $mes, $ano);
        $TotalDespesasSemCartao = $this->model->somarDespesas($idUsuario, $mes, $ano);

        // Categorias por cartão para tooltip
        $categoriasPorCartao = [];
        foreach ($gastosCartoes as $cartao) {
            $idCartao = $cartao['id_cartao'] ?? null;
            if ($idCartao) {
                $categoriasPorCartao[$idCartao] = $this->model->buscarCategoriasPorCartao($idUsuario, $idCartao, $mes, $ano);
            }
        }
        $receitasRecebidas = $this->model->getTotalReceitasRecebidas($idUsuario, $mes, $ano);
        $receitasPendentes = $this->model->getTotalReceitasPendentes($idUsuario, $mes, $ano);
        $listarReceitasPorCategoria = $this->model->listarReceitasPorCategoria($idUsuario, $mes, $ano);
        $receitasPorMes = $this->model->getReceitasPorMes($idUsuario);
        $despesasPorMes = $this->model->getDespesasPorMes($idUsuario);
        $saldoFavorita = $this->model->getSaldoContaFavorita($idUsuario);

        // Novos dados para faturas
        $faturasPendentes = $this->model->somarFaturasPendentes($idUsuario, $mes, $ano);
        $gastosCategoriasFatura = $this->model->listarGastosPorCategoriaFatura($idUsuario, $mes, $ano);
        $gastosSubcatsPorCatFatura = [];
        foreach ($gastosCategoriasFatura as $cat) {
            $idCat = $cat['id_categoria'];
            $gastosSubcatsPorCatFatura[$idCat] = $this->model->buscarSubcategoriasPorCategoriaFatura($idUsuario, $idCat, $mes, $ano);
        }

        // ===== MÉTRICAS DE TENDÊNCIA =====
        $receitasMesAnterior = $this->model->getReceitasMesAnterior($idUsuario, $mes, $ano);
        $despesasPagasMesAnterior = $this->model->getDespesasPagasMesAnterior($idUsuario, $mes, $ano);
        $gastosCartaoMesAnterior = $this->model->getGastosCartaoMesAnterior($idUsuario, $mes, $ano);

        // ===== KPIs FINANCEIROS =====
        $taxaPoupanca = $this->model->getTaxaPoupanca($idUsuario, $mes, $ano);
        $taxaEndividamento = $this->model->getTaxaEndividamento($idUsuario, $mes, $ano);
        $liquidez = $this->model->getLiquidez($idUsuario, $mes, $ano);
        $eficienciaPagamentos = $this->model->getEficienciaPagamentos($idUsuario, $mes, $ano);

        // ===== ANÁLISE AVANÇADA DE CARTÕES =====
        $cartoesComLimites = $this->model->getCartoesComLimites($idUsuario);
        $cartaoMaisUsado = $this->model->getCartaoMaisUsado($idUsuario, $mes, $ano);
        $cartoesProximosLimite = $this->model->getCartoesProximosLimite($idUsuario);
        $analiseBandeiras = $this->model->getAnaliseBandeiras($idUsuario, $mes, $ano);

        // ===== SISTEMA DE ALERTAS E RECOMENDAÇÕES =====
        $alertasFinanceiros = $this->model->getAlertasFinanceiros($idUsuario, $mes, $ano);
        $recomendacoesFinanceiras = $this->model->getRecomendacoesFinanceiras($idUsuario, $mes, $ano);
        $scoreFinanceiro = $this->model->getScoreFinanceiro($idUsuario, $mes, $ano);

        $gastosSubcatsPorCat = [];
        foreach ($gastosCategorias as $cat) {
            $idCat = $cat['id_categoria'];
            $gastosSubcatsPorCat[$idCat] = $this->model->buscarDespesasPorSubcategoria($idUsuario, $idCat, $mes, $ano);
        }

        return [
            'saldoFavorita' => $saldoFavorita,
            'totalCartao' => $totalCartao,
            'receitasPorMes' => $receitasPorMes,
            'despesasPorMes' => $despesasPorMes,
            'totalPendentes' => $totalPendentes,
            'totalPagos' => $totalPagos,
            'gastosCartoes' => $gastosCartoes,
            'gastosCategorias' => $gastosCategorias,
            'gastosSubcatsPorCat' => $gastosSubcatsPorCat,
            'TotalDespesasSemCartao' => $TotalDespesasSemCartao,
            'receitasRecebidas' => $receitasRecebidas,
            'receitasPendentes' => $receitasPendentes,
            'listarReceitasPorCategoria' => $listarReceitasPorCategoria,
            'faturasPendentes' => $faturasPendentes,
            'gastosCategoriasFatura' => $gastosCategoriasFatura,
            'gastosSubcatsPorCatFatura' => $gastosSubcatsPorCatFatura,
            'categoriasPorCartao' => $categoriasPorCartao,
            // Métricas de tendência
            'receitasMesAnterior' => $receitasMesAnterior,
            'despesasPagasMesAnterior' => $despesasPagasMesAnterior,
            'gastosCartaoMesAnterior' => $gastosCartaoMesAnterior,
            // KPIs financeiros
            'taxaPoupanca' => $taxaPoupanca,
            'taxaEndividamento' => $taxaEndividamento,
            'liquidez' => $liquidez,
            'eficienciaPagamentos' => $eficienciaPagamentos,
            // Análise avançada de cartões
            'cartoesComLimites' => $cartoesComLimites,
            'cartaoMaisUsado' => $cartaoMaisUsado,
            'cartoesProximosLimite' => $cartoesProximosLimite,
            'analiseBandeiras' => $analiseBandeiras,
            // Sistema de alertas e recomendações
            'alertasFinanceiros' => $alertasFinanceiros,
            'recomendacoesFinanceiras' => $recomendacoesFinanceiras,
            'scoreFinanceiro' => $scoreFinanceiro,
            'mesAtual' => $mes,
            'anoAtual' => $ano
        ];
    }
}
