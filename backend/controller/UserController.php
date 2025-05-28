<?php
<<<<<<< HEAD
// backend/controller/UserController.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../utils/Logger.php';

$userModel = new UserModel();
$logger = new Logger();

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        // --- INÍCIO DO NOVO DEBUG E LÓGICA DE ROTEAMENTO ---
        // Limpa o REQUEST_URI para pegar apenas o "caminho" relevante após UserController.php
        $script_name = basename($_SERVER['SCRIPT_NAME']); // Deve ser "UserController.php"
        $base_path = '/loginmvc/backend/controller/'; // O caminho do seu controlador no htdocs
        $relative_uri = str_replace($base_path . $script_name, '', $_SERVER['REQUEST_URI']);

        // Verifica se a requisição é para /login (considerando o pathinfo)
        $isLoginRequest = (strpos($relative_uri, '/login') !== false);

        error_log("DEBUG: FULL_REQUEST_URI: " . $_SERVER['REQUEST_URI']);
        error_log("DEBUG: RELATIVE_URI (after script name): " . $relative_uri);
        error_log("DEBUG: IS_LOGIN_REQUEST: " . ($isLoginRequest ? 'TRUE' : 'FALSE'));

        // --- FIM DO NOVO DEBUG E LÓGICA DE ROTEAMENTO ---

        if (!isset($data->email) || !isset($data->senha)) {
            echo json_encode(["erro" => "E-mail e senha são obrigatórios."]);
            $logger->error("Tentativa de requisição POST sem e-mail/senha.", ['request_data' => $data]);
            http_response_code(400); // Bad Request
            exit();
        }

        $email = $data->email;
        $senha = $data->senha;

        // Usa a nova variável de controle para rotear
        if ($isLoginRequest) {
            error_log("DEBUG: Entrando no bloco de LOGIN"); // Log extra
            // Lógica de Login
            $usuario = $userModel->buscarUsuarioPorEmailESenha($email, $senha);

            if ($usuario) {
                echo json_encode(["mensagem" => "Login bem-sucedido!", "usuario" => ["id" => $usuario['id'], "email" => $usuario['email']]]);
                $logger->info("Login bem-sucedido para o usuário: " . $email);
                http_response_code(200); // OK
            } else {
                echo json_encode(["erro" => "E-mail ou senha inválidos."]);
                $logger->warning("Falha no login para o e-mail: " . $email);
                http_response_code(401); // Unauthorized
            }
        } else {
            error_log("DEBUG: Entrando no bloco de CADASTRO"); // Log extra
            // Lógica de Cadastro
            // Primeiro, verifique se o usuário já existe para evitar duplicidade
            if ($userModel->buscarUsuarioPorEmailESenha($email, '')) {
                echo json_encode(["erro" => "Este e-mail já está cadastrado."]);
                $logger->warning("Tentativa de cadastro com e-mail já existente: " . $email);
                http_response_code(409); // Conflict
                exit();
            }

            if ($userModel->salvarUsuario($email, $senha)) {
                echo json_encode(["mensagem" => "Usuário salvo com sucesso"]);
                $logger->info("Novo usuário cadastrado: " . $email);
                http_response_code(201); // Created
            } else {
                echo json_encode(["erro" => "Não foi possível salvar o usuário."]);
                $logger->error("Erro ao tentar salvar novo usuário: " . $email);
                http_response_code(500); // Internal Server Error
            }
        }
        break;

    case 'OPTIONS':
        http_response_code(200);
        break;

    default:
        echo json_encode(["erro" => "Método não permitido."]);
        $logger->error("Método de requisição não permitido: " . $request_method);
        http_response_code(405); // Method Not Allowed
        break;
}
?>
=======
require_once '../model/UserModel.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // libera o acesso do React
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 👉 Resposta só pra testar no navegador (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(["mensagem" => "Controller funcionando"]);
    exit;
}

// 👉 Fluxo normal para receber dados do React (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (isset($dados['email']) && isset($dados['senha'])) {
        $userModel = new UserModel();
        $resultado = $userModel->salvarUsuario($dados['email'], $dados['senha']);
        echo json_encode($resultado);
    } else {
        echo json_encode(["erro" => "Campos incompletos."]);
    }
}

>>>>>>> 6a1e99a490e7a70324a1eb194a411ddde497eaa0
