<?php
require_once BASE_PATH . '/app/models/ReceitasModel.php';

class ReceitasController
{
    private $model;

    public function __construct()
    {
        $this->model = new ReceitasModel();
    }

    public function listar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        require_once BASE_PATH . '/app/models/DespesasModel.php';

        $idUsuario = usuarioLogado()['id_usuario'];

        $receitas = $this->model->buscarTodos($idUsuario);

        $despModel = new DespesasModel();
        $contas = $despModel->buscarContas($idUsuario);
        $cartoes = $despModel->buscarCartoes($idUsuario);

        $categoriasReceitas = $this->model->buscarCategoriasReceita($idUsuario);
        $subcategorias = $this->model->buscarSubcategoriasReceita($idUsuario);
        $formasReceita = $this->model->buscarFormasTransacaoReceita($idUsuario);

        $categoriasUsadas = array_unique(array_map(function ($r) {
            return strtolower(trim($r['nome_categoria']));
        }, $receitas));


        return [
            'receitas' => $receitas,
            'categoriasReceitas' => $categoriasReceitas,
            'subcategorias' => $subcategorias,
            'formasReceita' => $formasReceita,
            'contas' => $contas,
            'cartoes' => $cartoes,
            'categoriasUsadas' => $categoriasUsadas
        ];
    }

    public function salvar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $dados = $_POST;
        $dados['id_usuario'] = $idUsuario;

        $sucesso = $this->model->salvarReceita($dados);

        echo json_encode(['sucesso' => $sucesso]);
    }

    public function atualizar()
    {
        verificarLogin();
        $idUsuario = usuarioLogado()['id_usuario'];

        $dados = $_POST;
        $dados['id_usuario']  = $idUsuario;
        // pega o escopo enviado pelo radio-group do modal:
        $dados['modo_edicao'] = $_POST['escopo_edicao'] ?? 'somente';

        $sucesso = $this->model->atualizarReceita($dados);
        echo json_encode(['sucesso' => $sucesso]);
    }

    public function excluir()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $id = $_POST['id'] ?? null;
        $escopo = $_POST['escopo'] ?? 'somente';

        if (!$id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID ausente']);
            return;
        }

        $sucesso = $this->model->excluirReceita($id, $idUsuario, $escopo);
        echo json_encode(['sucesso' => $sucesso]);
    }
}
