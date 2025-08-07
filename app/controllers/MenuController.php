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
        $idUsuario = usuarioLogado()['id_usuario'];

        $totalCartao = $this->model->somarDespesasCartao($idUsuario);
        $totalPendentes = $this->model->somarDespesasPendentes($idUsuario);
        $totalPagos = $this->model->somarDespesasPagas($idUsuario);
        $gastosCartoes = $this->model->listarGastosPorCartao($idUsuario);
        $gastosCategorias = $this->model->listarGastosPorCategoria($idUsuario);
        $TotalDespesasSemCartao = $this->model->somarDespesas($idUsuario);
        $receitasRecebidas = $this->model->getTotalReceitasRecebidas($idUsuario);
        $receitasPendentes = $this->model->getTotalReceitasPendentes($idUsuario);
        $listarReceitasPorCategoria = $this->model->listarReceitasPorCategoria($idUsuario);
        $receitasPorMes = $this->model->getReceitasPorMes($idUsuario);
        $despesasPorMes = $this->model->getDespesasPorMes($idUsuario);
        $saldoFavorita = $this->model->getSaldoContaFavorita($idUsuario);
        $gastosSubcatsPorCat = [];
        foreach ($gastosCategorias as $cat) {
            $idCat = $cat['id_categoria'];
            $gastosSubcatsPorCat[$idCat] = $this->model->buscarDespesasPorSubcategoria($idUsuario, $idCat);
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
            'listarReceitasPorCategoria' => $listarReceitasPorCategoria
        ];
    }
}
