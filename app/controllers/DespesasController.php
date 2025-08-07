<?php
require_once BASE_PATH . '/app/models/DespesasModel.php';

class DespesasController
{

    private $model;

    public function __construct()
    {
        $this->model = new DespesasModel();
    }


    public function listar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $despesas = $this->model->buscarTodos($idUsuario);
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
    public function salvar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $dados = $_POST;
        $dados['id_usuario'] = $idUsuario;

        $sucesso = $this->model->salvarDespesa($dados);

        echo json_encode(['sucesso' => $sucesso]);
    }

    public function atualizar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $dados = $_POST;
        $dados['id_usuario'] = $idUsuario;
        $dados['modo_edicao'] = $_POST['modo_edicao'] ?? 'somente';

        $sucesso = $this->model->atualizarDespesa($dados);

        echo json_encode(['sucesso' => $sucesso]);
    }

    public function pagar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $idDespesa     = isset($_POST['id_despesa'])     ? (int) $_POST['id_despesa']     : null;
        $dataPagamento = $_POST['data_pagamento']        ?? null;
        $idConta       = isset($_POST['id_conta'])       ? (int) $_POST['id_conta']       : null;
        $valor         = isset($_POST['valor'])          ? (float) str_replace(',', '.', $_POST['valor']) : 0.00;

        if (!$idDespesa || !$dataPagamento || !$idConta || !$valor) {
            echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
            return;
        }

        $sucesso = $this->model->confirmarPagamento(
            $idDespesa,
            $idUsuario,
            $dataPagamento,
            $idConta,
            $valor
        );

        echo json_encode([
            'sucesso' => $sucesso,
            'erro'    => $sucesso ? null : 'Falha no pagamento. Confira dados/saldo.'
        ]);
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

        $sucesso = $this->model->excluirDespesa($id, $idUsuario, $escopo);
        echo json_encode(['sucesso' => $sucesso]);
    }

    // Despesas de Fatura

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
        require_once BASE_PATH . '/app/models/CategoriaModel.php';
        require_once BASE_PATH . '/app/models/CartoesModel.php';

        $categoriaModel = new CategoriaModel();
        $cartaoModel = new CartoesModel();

        $categorias = $categoriaModel->listarCategorias($idUsuario);
        $subcategorias = $categoriaModel->listarSubcategorias($idUsuario);
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

    public function pagarFatura()
    {

        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $idCartao = $_POST['id_cartao'];
        $idConta = $_POST['id_conta'];

        $mes = $_POST['mes'] ?? date('n');
        $ano = $_POST['ano'] ?? date('Y');

        $model = new DespesasModel();

        $valorTotal = $model->getValorFaturaMensal($idCartao, $idUsuario, $mes, $ano);

        if ($valorTotal <= 0) {
            http_response_code(400);
            echo "Nenhuma fatura em aberto.";
            return;
        }

        $success = $model->quitarFatura($idCartao, $idConta, $valorTotal, $idUsuario, $mes, $ano);


        if ($success) {
            echo "OK";
        } else {
            http_response_code(500);
            echo "Erro ao pagar fatura";
        }
    }

    public function atualizarDespesaCartao()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $dados = $_POST;
        $dados['id_usuario'] = $idUsuario;

        $modo = $_POST['modo_edicao'] ?? 'atual';

        if (empty($dados['id_despesa'])) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID da despesa não informado']);
            return;
        }

        $sucesso = $this->model->atualizarDespesaCartao($dados, $modo);

        echo json_encode(['sucesso' => $sucesso]);
    }

    public function estornarFatura()
    {
        try {
            require_once BASE_PATH . '/app/helpers/AuthHelper.php';
            require_once BASE_PATH . '/app/helpers/logger.php';

            logDespesas("estornarFatura >> POST: " . json_encode($_POST), 'DEBUG');


            $idUsuario       = usuarioLogado()['id_usuario'];
            $idCartao        = intval($_POST['id_cartao']);
            $idConta         = intval($_POST['id_conta']);
            $mes             = $_POST['mes'] ?? date('n');
            $ano             = $_POST['ano'] ?? date('Y');
            $idCategoria     = intval($_POST['id_categoria']);
            $idSubcategoria  = intval($_POST['id_subcategoria']);
            $idForma         = intval($_POST['id_forma_transacao']);

            logDespesas("estornarFatura >> params: idUsuario={$idUsuario}, idCartao={$idCartao}, idConta={$idConta}, mes={$mes}, ano={$ano}, cat={$idCategoria}, subcat={$idSubcategoria}, forma={$idForma}", 'DEBUG');

            $model = new DespesasModel();
            $valor = $model->getValorFaturaMensal($idCartao, $idUsuario, $mes, $ano);
            if ($valor <= 0) {
                logDespesas("estornarFatura >> nenhum valor pendente para estornar (valor={$valor})", 'WARN');
                http_response_code(400);
                echo json_encode([
                    'sucesso'  => false,
                    'mensagem' => 'Sem fatura para estornar'
                ]);
                return;
            }

            // chama o Model passando tudo
            $ok = $model->estornarFatura(
                $idCartao,
                $idConta,
                $valor,
                $idUsuario,
                $mes,
                $ano,
                $idCategoria,
                $idSubcategoria,
                $idForma
            );
            logDespesas("estornarFatura >> model->estornarFatura retornou " . ($ok ? 'true' : 'false'), 'DEBUG');

            header('Content-Type: application/json; charset=utf-8');
            if ($ok) {
                echo json_encode(['sucesso' => true]);
            } else {
                logDespesas("estornarFatura >> falha ao chamar estornarFatura no model", 'ERROR');
                http_response_code(500);
                echo json_encode([
                    'sucesso'  => false,
                    'mensagem' => 'Falha no estorno'
                ]);
            }
        } catch (\Throwable $e) {
            logDespesas("Erro fatal em estornarFatura: " . $e->getMessage(), 'ERROR');
            error_log(
                "Erro fatal em estornarFatura: "
                    . $e->getMessage()
                    . " em {$e->getFile()} na linha {$e->getLine()}"
            );
            http_response_code(500);
            echo json_encode([
                'sucesso'  => false,
                'mensagem' => 'Falha no estorno'
            ]);
        }
    }
}
