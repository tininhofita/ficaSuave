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
        $categoriasUsadas = array_unique(array_map(function ($d) {
            return strtolower(trim($d['nome_categoria']));
        }, $despesas));

        return [
            'despesas' => $despesas,
            'categorias' => $categorias,
            'subcategorias' => $subcategorias,
            'formas' => $formas,
            'contas' => $contas,
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
        try {
            require_once BASE_PATH . '/app/helpers/AuthHelper.php';
            $idUsuario = usuarioLogado()['id_usuario'];

            // Validação dos dados
            $idDespesa = isset($_POST['id_despesa']) ? (int) $_POST['id_despesa'] : 0;
            $dataPagamento = $_POST['data_pagamento'] ?? '';
            $idConta = isset($_POST['id_conta']) ? (int) $_POST['id_conta'] : 0;
            // Processa valor no formato brasileiro (1.884,39 -> 1884.39)
            $valor = isset($_POST['valor']) ? $this->parseBrazilianCurrency($_POST['valor']) : 0.00;

            // Validações
            if (!$idDespesa) {
                throw new \Exception('ID da despesa é obrigatório');
            }
            if (empty($dataPagamento)) {
                throw new \Exception('Data de pagamento é obrigatória');
            }
            if (!$idConta) {
                throw new \Exception('Conta bancária é obrigatória');
            }
            if ($valor <= 0) {
                throw new \Exception('Valor deve ser maior que zero');
            }

            // Validar formato da data
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataPagamento)) {
                throw new \Exception('Formato de data inválido');
            }

            $sucesso = $this->model->confirmarPagamento(
                $idDespesa,
                $idUsuario,
                $dataPagamento,
                $idConta,
                $valor
            );

            if ($sucesso) {
                echo json_encode(['sucesso' => true]);
            } else {
                throw new \Exception('Falha ao confirmar pagamento. Verifique os dados e tente novamente.');
            }
        } catch (\Throwable $e) {
            error_log('[DespesasController::pagar] ' . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ]);
        }
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

    /**
     * Converte valor no formato brasileiro para float
     * Ex: "1.884,39" -> 1884.39
     * Ex: "1.884.39" -> 1884.39 (caso especial onde ponto é decimal)
     */
    private function parseBrazilianCurrency(string $value): float
    {
        // Remove espaços
        $value = trim($value);

        // Se tem vírgula, assume formato brasileiro padrão (1.884,39)
        if (strpos($value, ',') !== false) {
            // Remove pontos (separadores de milhares) e substitui vírgula por ponto (decimal)
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            // Se não tem vírgula, assume formato com ponto decimal (1.884.39)
            // Neste caso, remove apenas os pontos de milhares (mantém o último ponto como decimal)
            $parts = explode('.', $value);
            if (count($parts) > 1) {
                // Remove todos os pontos exceto o último
                $integerPart = implode('', array_slice($parts, 0, -1));
                $decimalPart = end($parts);
                $value = $integerPart . '.' . $decimalPart;
            }
        }

        return (float) $value;
    }
}
