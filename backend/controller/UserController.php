<?php
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

