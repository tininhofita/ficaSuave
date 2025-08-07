<?php

require_once BASE_PATH . '/app/models/CategoriaModel.php';
require_once BASE_PATH . '/app/helpers/logger.php';
require_once BASE_PATH . '/app/helpers/AuthHelper.php';

class CategoriaController
{
    private $model;

    public function __construct()
    {
        $this->model = new CategoriaModel();
    }

    public function listar(): array
    {
        verificarLogin();
        $idUsuario = $_SESSION['user_id'];

        return [
            'categorias' => $this->model->listarComSubcategorias($idUsuario),
            'idUsuarioLogado' => $idUsuario
        ];
    }





    public function excluir()
    {
        verificarLogin();
        $idUsuario = $_SESSION['id_usuario'];

        $idCategoria = $_POST['id_categoria'] ?? null;
        $categoria = $this->model->buscarPorId($idCategoria);

        if (!$categoria) {
            logEvento("Categoria inexistente para exclusão: ID $idCategoria", "WARNING");
            header('Location: /categorias');
            return;
        }

        if ($categoria['id_usuario'] === $idUsuario) {
            $this->model->excluir($idCategoria);
        } elseif ($categoria['categoria_padrao']) {
            $this->model->desativar($idCategoria);
        }

        header('Location: /categorias');
    }
}
