<?php
require_once BASE_PATH . '/app/models/FormaTransacaoModel.php';

class FormaTransacaoController
{
    private $model;

    public function __construct()
    {
        $this->model = new FormaTransacaoModel();
    }

    public function listar()
    {
        /** @return array */
        $formas = $this->model->buscarTodas();
        return ['formas' => $formas];
    }

    public function salvar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];
        $dados = $_POST;

        if (!empty($dados['id_forma_transacao'])) {
            $this->model->atualizar([
                'id' => $dados['id_forma_transacao'],
                'nome' => $dados['nome'],
                'tipo' => $dados['tipo'],
                'uso' => $dados['uso'],
                'ativa' => isset($dados['ativa']) ? 1 : 0
            ]);
        } else {
            $this->model->inserir([
                'id_usuario' => $idUsuario,
                'nome' => $dados['nome'],
                'tipo' => $dados['tipo'],
                'uso' => $dados['uso'],
                'ativa' => isset($dados['ativa']) ? 1 : 0
            ]);
        }

        header('Location: /formas-transacao');
        exit;
    }



    public function excluir()
    {
        $id = $_POST['id'];
        $this->model->excluir($id);
        header('Location: /formas-transacao');
        exit;
    }
}
