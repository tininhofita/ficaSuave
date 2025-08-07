<?php

require_once BASE_PATH . '/app/models/CadastroModel.php';
require_once BASE_PATH . '/app/helpers/logger.php';

class CadastroController
{
    public function registrar()
    {
        header('Content-Type: application/json');

        $nome     = $_POST['nome'] ?? '';
        $email    = $_POST['email'] ?? '';
        $senha    = $_POST['senha'] ?? '';
        $cep      = $_POST['cep'] ?? '';
        $rua      = $_POST['rua'] ?? '';
        $cidade   = $_POST['cidade'] ?? '';
        $estado   = $_POST['estado'] ?? '';
        $uf       = $_POST['uf'] ?? '';
        $pais     = $_POST['pais'] ?? '';
        $renda    = $_POST['renda'] ?? null;

        if (!$nome || !$email || !$senha || !$cep || !$rua || !$cidade || !$estado || !$uf || !$pais) {
            echo json_encode(['success' => false, 'error' => 'Preencha todos os campos obrigatórios.']);
            return;
        }

        $usuarioModel = new UsuarioModel();

        if ($usuarioModel->existeEmail($email)) {
            logEvento("Tentativa de cadastro com e-mail já usado: $email", "WARNING");
            echo json_encode(['success' => false, 'error' => 'Este e-mail já está em uso.']);
            return;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $inserido = $usuarioModel->inserirUsuario([
            'nome' => $nome,
            'email' => $email,
            'senha_hash' => $senhaHash,
            'cep' => $cep,
            'rua' => $rua,
            'cidade' => $cidade,
            'estado' => $estado,
            'uf' => $uf,
            'pais' => $pais,
            'renda' => $renda
        ]);

        if (!$inserido) {
            echo json_encode(['success' => false, 'error' => 'Erro ao salvar cadastro.']);
            return;
        }

        logEvento("Novo cadastro: $email");
        echo json_encode(['success' => true]);
    }
}
