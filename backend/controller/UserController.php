<?php
// backend/controller/UserController.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Caminho correto

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$userModel = new UserModel();
$logger = new Logger();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$requestMethod = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriSegments = explode('/', trim($uri, '/'));

$controllerBaseIndex = array_search('controller', $uriSegments);

$controllerFile = isset($uriSegments[$controllerBaseIndex + 1]) ? $uriSegments[$controllerBaseIndex + 1] : null;
$action = isset($uriSegments[$controllerBaseIndex + 2]) ? $uriSegments[$controllerBaseIndex + 2] : null;
$resourceId = isset($uriSegments[$controllerBaseIndex + 3]) ? $uriSegments[$controllerBaseIndex + 3] : null;


if ($requestMethod === 'GET' && empty($action)) {
    echo json_encode(["mensagem" => "Controller funcionando: " . ($controllerFile ?? 'N/A')]);
    exit;
}


if ($requestMethod === 'POST' && $action === 'login') {
    $dados = json_decode(file_get_contents("php://input"), true);
    $username = $dados['username'] ?? '';
    $senha = $dados['senha'] ?? '';

    if (empty($username) || empty($senha)) {
        $logger->log(Logger::WARNING, "Tentativa de login com campos incompletos.");
        http_response_code(400);
        echo json_encode(["erro" => "Nome de usuário e senha são obrigatórios."]);
        exit;
    }

    $usuario = $userModel->buscarUsuarioPorUsername($username);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $logger->log(Logger::INFO, "Login bem-sucedido para o usuario: " . $username);
        
        $secret_key = 'NK/tEnnNSGQBCpfv7eBj6Knta/LBOAM6dijxyNZJYr8=';
        $issuer_claim = "http://localhost";
        $audience_claim = "http://localhost";
        $issuedat_claim = time();
        $notbefore_claim = $issuedat_claim;
        $expire_claim = $issuedat_claim + (3600 * 24);

        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "user_id" => $usuario['id'], // <--- ESTE É O ID QUE VAI PARA O TOKEN
                "username" => $usuario['username'],
                "profile" => $usuario['role']
            )
        );

        $jwt = JWT::encode($token, $secret_key, 'HS256');

        echo json_encode([
            "mensagem" => "Login bem-sucedido!",
            "jwt" => $jwt,
            "usuario" => [
                "id" => $usuario['id'],
                "username" => $usuario['username'],
                "role" => $usuario['role']
            ]
        ]);

    } else {
        $logger->log(Logger::WARNING, "Tentativa de login falhou para o username: " . $username);
        http_response_code(401);
        echo json_encode(["erro" => "Nome de usuário ou senha inválidos."]);
    }
    exit;
}

if ($requestMethod === 'POST' && empty($action)) {
    $dados = json_decode(file_get_contents("php://input"), true);
    $username = $dados['username'] ?? '';
    $senha = $dados['senha'] ?? '';
    $role = $dados['role'] ?? 'user';

    if (empty($username) || empty($senha)) {
        http_response_code(400);
        echo json_encode(["erro" => "Campos incompletos (nome de usuário e senha são obrigatórios)."]);
        exit;
    }

    $resultado = $userModel->salvarUsuario($username, $senha, $role);
    if (isset($resultado['erro'])) {
        http_response_code(409);
    } else {
        http_response_code(201);
    }
    echo json_encode($resultado);
    exit;
}

if ($requestMethod === 'GET' && $action === 'users') {
    $userData = Auth::validateToken();
    if (!$userData || ($userData['profile'] ?? null) !== 'admin') {
        http_response_code(403);
        echo json_encode(["erro" => "Acesso negado. Apenas administradores podem listar usuários."]);
        exit();
    }
    $usuarios = $userModel->lerTodosUsuarios();
    echo json_encode($usuarios);
    exit;
}

if ($requestMethod === 'PUT' && $action === 'users' && $resourceId && (isset($uriSegments[$controllerBaseIndex + 4]) && $uriSegments[$controllerBaseIndex + 4] === 'role')) {
    $userData = Auth::validateToken();
    if (!$userData || ($userData['profile'] ?? null) !== 'admin') {
        http_response_code(403);
        echo json_encode(["erro" => "Acesso negado. Apenas administradores podem atualizar perfis."]);
        exit();
    }
    $dados = json_decode(file_get_contents("php://input"), true);
    $novo_perfil = $dados['role'] ?? null;

    if (!$novo_perfil) {
        http_response_code(400);
        echo json_encode(["erro" => "Novo perfil não especificado."]);
        exit();
    }

    $resultado = $userModel->atualizarPerfilUsuario($resourceId, $novo_perfil);
    if (isset($resultado['erro'])) {
        http_response_code(500);
    } else {
        http_response_code(200);
    }
    echo json_encode($resultado);
    exit;
}

http_response_code(404);
echo json_encode(["erro" => "Rota nao encontrada ou metodo nao permitido."]);

