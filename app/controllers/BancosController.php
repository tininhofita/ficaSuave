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

    public function favoritar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        header('Content-Type: application/json; charset=utf-8');

        try {
            $user = usuarioLogado();
            if (!$user || empty($user['id_usuario'])) {
                http_response_code(401);
                echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
                exit;
            }

            $idUsuario = (int)$user['id_usuario'];
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['sucesso' => false, 'erro' => 'ID inválido']);
                exit;
            }

            // toggle: se já é favorita → desfaz; senão → zera outras e define esta
            $ok = $this->model->definirFavoritaToggle($id, $idUsuario);
            echo json_encode(['sucesso' => (bool)$ok]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro interno']);
            exit;
        }
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
