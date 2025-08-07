<?php
require_once BASE_PATH . '/app/models/CartoesModel.php';

class CartoesController
{

    private $model;

    public function __construct()
    {
        $this->model = new CartoesModel();
    }
    public function listar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        require_once BASE_PATH . '/app/models/BancosModel.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        // 1️⃣ pega filtro
        $filter = $_GET['filtro-fatura'] ?? 'aberta'; // 'aberta' ou 'fechada'

        // 2️⃣ carrega todos os cartões
        $cartoes = $this->model->listarPorUsuario($idUsuario);

        $hoje = new \DateTime();

        foreach ($cartoes as &$c) {
            // 🔹 data de fechamento DESTE mês
            $dfEsteMes = (clone $hoje)
                ->setDate(
                    (int)$hoje->format('Y'),
                    (int)$hoje->format('n'),
                    (int)$c['dia_fechamento']
                );
            // 1) a fatura atual está fechada se hoje >= dia de fechamento
            $c['is_closed_this_month'] = $hoje >= $dfEsteMes;

            // 🔹 data de exibição do fechamento (próxima)
            $dfExibir = clone $dfEsteMes;
            if ($hoje >= $dfEsteMes) {
                $dfExibir->modify('+1 month');
            }
            $c['fecha_este_mes_dt']    = $dfEsteMes;
            $c['fecha_proximo_dt']     = $dfExibir;

            // 🔹 mesmo para vencimento (se você quiser usar vencimento diferente do fechamento)
            $dvEsteMes = (clone $hoje)
                ->setDate(
                    (int)$hoje->format('Y'),
                    (int)$hoje->format('n'),
                    (int)$c['vencimento_fatura']
                );
            $dvExibir = clone $dvEsteMes;
            if ($hoje >= $dvEsteMes) {
                $dvExibir->modify('+1 month');
            }
            $c['vence_este_mes_dt']    = $dvEsteMes;
            $c['vence_proximo_dt']     = $dvExibir;

            // 🔹 gastos e limites
            $c['gastos_pendentes']    = $this->model->calcularGastosPendentesCartao($c['id_cartao']);
            $c['limite_disponivel']   = $c['limite'] - $c['gastos_pendentes'];

            // 🔹 valores de fatura
            // fatura corrente (do dia “vence_este_mes_dt”)
            $c['fatura_atual']        = $this->model->calcularFaturaPorVencimento(
                $c['id_cartao'],
                $c['vence_este_mes_dt']->format('Y-m-d')
            );
            // fatura seguinte (no dia “vence_proximo_dt”)
            $c['fatura_proxima']      = $this->model->calcularFaturaPorVencimento(
                $c['id_cartao'],
                $c['vence_proximo_dt']->format('Y-m-d')
            );
        }
        unset($c);

        // filtra
        if ($filter === 'fechada') {
            // só quem já fechou ESTE mês
            $cartoes = array_filter($cartoes, fn($c) => $c['is_closed_this_month']);
        }
        // se for “aberta”, deixa TODOS — no view iremos exibir a próxima fatura para quem já fechou

        // 4ordena por data apropriada
        usort($cartoes, function ($a, $b) use ($filter) {
            if ($filter === 'aberta') {
                // ordena pelo dia de fechamento que realmente vamos exibir
                return $a['fecha_proximo_dt'] <=> $b['fecha_proximo_dt'];
            }
            // fechadas → ordena pela data de fechamento DESTE mês
            return $a['fecha_este_mes_dt'] <=> $b['fecha_este_mes_dt'];
        });

        // contas para o modal “novo cartão”
        $contas = (new BancosModel())->listarContasPorUsuario($idUsuario);


        return [
            'cartoes' => $cartoes,
            'contas'  => $contas,
            'filter'  => $filter,
        ];
    }




    public function salvar()
    {
        require_once BASE_PATH . '/app/helpers/AuthHelper.php';
        $idUsuario = usuarioLogado()['id_usuario'];

        $dados = $_POST;
        $dados['id_usuario'] = $idUsuario;

        if (!empty($dados['id_cartao'])) {
            $this->model->atualizar($dados);
        } else {
            $this->model->inserir($dados);
        }

        header('Location: /cartoes');
        exit;
    }

    public function excluir()
    {
        $id = $_POST['id'];
        $this->model->excluir($id);
        header('Location: /cartoes');
        exit;
    }
}
