<?php
require_once BASE_PATH . '/app/models/FaturaModel.php';

class FaturaController
{
    private $model;

    public function __construct()
    {
        $this->model = new FaturaModel();
    }

    public function listar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $despesas = $this->model->buscarTodas($idUsuario);
        $categorias = $this->model->buscarCategorias($idUsuario);
        $subcategorias = $this->model->buscarSubcategorias($idUsuario);
        $formas = $this->model->buscarFormasTransacao($idUsuario);
        $contas = $this->model->buscarContas($idUsuario);
        $cartoes = $this->model->buscarCartoes($idUsuario);

        $categoriasUsadas = array_unique(array_map(function ($d) {
            return strtolower(trim($d['nome_categoria']));
        }, $despesas));


        return [
            'despesas' => $despesas,
            'categorias' => $categorias,
            'subcategorias' => $subcategorias,
            'formas' => $formas,
            'contas' => $contas,
            'cartoes' => $cartoes,
            'categoriasUsadas' => $categoriasUsadas
        ];
    }

    public function listarPorCartao($idCartao)
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        require_once BASE_PATH . '/app/models/BancosModel.php';

        $idUsuario = usuarioLogado()['id_usuario'];

        $mes = $_GET['mes'] ?? date('n');
        $ano = $_GET['ano'] ?? date('Y');

        // Dados da fatura
        $despesas = $this->model->listarPorCartao($idUsuario, $idCartao, $mes, $ano);
        $cartao = $this->model->getInfoCartao($idUsuario, $idCartao);

        // Contas para pagamento
        $bancoModel = new BancosModel();
        $contas = $bancoModel->listarContasPorUsuario($idUsuario);

        // Fatura aberta?
        $hoje = new DateTime();
        if (!empty($cartao['data_fechamento_proxima'])) {
            $dataFechamento = DateTime::createFromFormat('Y-m-d', $cartao['data_fechamento_proxima']);
            $cartao['fatura_aberta'] = $dataFechamento && $hoje < $dataFechamento;
        } else {
            $cartao['fatura_aberta'] = false;
        }

        // 👇 Dados adicionais necessários para o modal funcionar
        require_once BASE_PATH . '/app/models/CartoesModel.php';

        $cartaoModel = new CartoesModel();

        // Usar métodos do próprio FaturaModel que já filtram por tipo 'despesa'
        $categorias = $this->model->buscarCategorias($idUsuario);
        $subcategorias = $this->model->buscarSubcategorias($idUsuario);
        $cartoes = $cartaoModel->listarPorUsuario($idUsuario);

        return compact(
            'despesas',
            'cartao',
            'contas',
            'categorias',
            'subcategorias',
            'cartoes'
        );
    }

    public function salvar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $dados = $_POST;
        $dados['id_usuario'] = $idUsuario;

        $sucesso = $this->model->salvarDespesaFatura($dados);

        echo json_encode(['sucesso' => $sucesso]);
    }

    public function excluir()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $id = (int)$_POST['id'];
        $escopo = $_POST['escopo'] ?? 'somente';

        $sucesso = $this->model->excluirDespesaFatura($idUsuario, $id, $escopo);

        echo json_encode(['sucesso' => $sucesso]);
    }

    public function atualizar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $dados = $_POST;
        $dados['id_usuario'] = $idUsuario;

        $sucesso = $this->model->atualizarDespesaFatura($dados);

        echo json_encode(['sucesso' => $sucesso]);
    }

    public function pagarFatura()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $dados = $_POST;
        $dados['id_usuario'] = $idUsuario;

        $resultado = $this->model->pagarFaturaCompleta($dados);

        if ($resultado['sucesso']) {
            echo json_encode([
                'sucesso' => true,
                'saldo_negativo' => $resultado['saldo_negativo'] ?? false,
                'saldo_atual' => $resultado['saldo_atual'] ?? 0
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => $resultado['erro'] ?? 'Erro ao processar pagamento']);
        }
    }
}
