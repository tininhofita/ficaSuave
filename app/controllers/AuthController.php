<?php

require_once BASE_PATH . '/app/helpers/logger.php';
require_once BASE_PATH . '/app/helpers/AuthHelper.php';
require_once BASE_PATH . '/app/models/AuthModel.php';

class AuthController
{
    private $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    public function login()
    {
        // Garante início da sessão ANTES de qualquer output
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Verificar se headers já foram enviados
        if (headers_sent()) {
            error_log("Erro: Headers já foram enviados antes do login");
            echo json_encode(['success' => false, 'error' => 'Erro interno. Tente novamente.']);
            exit;
        }

        header('Content-Type: application/json');

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Preencha todos os campos.']);
            exit;
        }

        $usuario = $this->authModel->buscarPorEmail($email);

        if (!$usuario || !password_verify($password, $usuario['senha_hash'])) {
            echo json_encode(['success' => false, 'error' => 'E-mail ou senha incorretos.']);
            exit;
        }

        $_SESSION['user_id'] = $usuario['id_usuario'];
        $_SESSION['nome_usuario'] = $usuario['nome_usuario'];

        $this->authModel->registrarLoginBemSucedido($usuario['id_usuario']);
        registrarLogin("Login realizado com sucesso", $email);
        logEvento("Login OK | ID: {$usuario['id_usuario']} | Nome: {$usuario['nome_usuario']} | Email: $email", 'INFO');

        echo json_encode(['success' => true, 'user_id' => $usuario['id_usuario']]);
        exit;
    }



    public function logout()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $email = $_SESSION['nome_usuario'] ?? 'Desconhecido';
        registrarLogin("Logout efetuado", $email);

        session_unset();
        session_destroy();

        header("Location: /login");
        exit();
    }
}
