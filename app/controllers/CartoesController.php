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

        // 1️⃣ pega filtro de mês
        $mesFiltro = $_GET['mes-filtro'] ?? date('Y-m'); // formato YYYY-MM

        // 2️⃣ carrega todos os cartões
        $cartoes = $this->model->listarPorUsuario($idUsuario);

        $hoje = new \DateTime();
        $mesFiltroObj = new \DateTime($mesFiltro . '-01'); // primeiro dia do mês filtrado

        // Calcula início e fim do mês filtrado
        $dataInicio = $mesFiltroObj->format('Y-m-01');
        $dataFim = $mesFiltroObj->format('Y-m-t'); // último dia do mês

        foreach ($cartoes as &$c) {
            // 🔹 data de fechamento do mês filtrado
            $dfMesFiltro = (clone $mesFiltroObj)
                ->setDate(
                    (int)$mesFiltroObj->format('Y'),
                    (int)$mesFiltroObj->format('n'),
                    (int)$c['dia_fechamento']
                );

            // 🔹 data de vencimento do mês filtrado
            $dvMesFiltro = (clone $mesFiltroObj)
                ->setDate(
                    (int)$mesFiltroObj->format('Y'),
                    (int)$mesFiltroObj->format('n'),
                    (int)$c['vencimento_fatura']
                );

            // 🔹 determina se a fatura está aberta ou fechada
            // Se hoje >= data de fechamento do mês filtrado, a fatura está fechada
            $c['fatura_fechada'] = $hoje >= $dfMesFiltro;
            $c['data_fechamento'] = $dfMesFiltro;
            $c['data_vencimento'] = $dvMesFiltro;

            // 🔹 gastos e limites
            $c['gastos_pendentes']    = $this->model->calcularGastosPendentesCartaoMes($c['id_cartao'], $dataInicio, $dataFim);
            $c['gastos_nao_pagos']    = $this->model->calcularGastosNaoPagosCartao($c['id_cartao']); // Para limite disponível
            $c['limite_disponivel']   = $c['limite'] - $c['gastos_nao_pagos'];

            // 🔹 status das despesas do mês filtrado
            $c['status_despesas_mes'] = $this->model->buscarStatusDespesasCartaoMes($c['id_cartao'], $dataInicio, $dataFim);

            // 🔹 valor da fatura do mês filtrado
            // Sempre calcula todas as despesas que vencem na data de vencimento da fatura
            // Independente do status (pendente, pago, atrasado)
            $c['fatura_valor'] = $this->model->calcularFaturaTotalPorVencimento(
                $c['id_cartao'],
                $dvMesFiltro->format('Y-m-d')
            );
        }
        unset($c);

        // 3️⃣ ordena por data de fechamento
        usort($cartoes, function ($a, $b) {
            return $a['data_fechamento'] <=> $b['data_fechamento'];
        });

        // 4️⃣ contas para o modal "novo cartão"
        $contas = (new BancosModel())->listarContasPorUsuario($idUsuario);

        // 5️⃣ gera lista de meses para o filtro
        $meses = [];
        $dataAtual = new \DateTime();
        $nomesMeses = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];

        for ($i = -6; $i <= 6; $i++) {
            $mes = (clone $dataAtual)->modify("$i months");
            $meses[] = [
                'valor' => $mes->format('Y-m'),
                'nome' => $nomesMeses[(int)$mes->format('n')] . ' ' . $mes->format('Y'),
                'selecionado' => $mes->format('Y-m') === $mesFiltro
            ];
        }

        return [
            'cartoes' => $cartoes,
            'contas'  => $contas,
            'mesFiltro' => $mesFiltro,
            'meses' => $meses,
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
