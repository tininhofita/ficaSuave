<?php
require_once BASE_PATH . '/app/models/BancosModel.php';

class BancosController
{

    private $model;
    public function __construct()
    {
        $this->model = new BancosModel();
    }
    public function listar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $bancos = $this->model->buscarTodos($idUsuario);

        return [
            'bancos' => $bancos
        ];
    }

    public function salvar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];
        $dados = $_POST;
        $dados['id_usuario'] = $idUsuario;

        if (!empty($dados['id_conta'])) {
            $this->model->atualizar($dados);
        } else {
            $this->model->inserir($dados);
        }

        header('Location: /bancos');
        exit;
    }

    public function excluir()
    {
        $id = $_POST['id'];
        $this->model->excluir($id);
        header('Location: /bancos');
        exit;
    }
}
