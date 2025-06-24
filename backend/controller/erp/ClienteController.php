<?php
// backend/controller/erp/ClienteController.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../model/erp/ModeloCliente.php';
require_once __DIR__ . '/../../utils/Logger.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$modeloCliente = new ModeloCliente();
$logger = new Logger();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$requestMethod = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriSegments = explode('/', trim($uri, '/'));

$erpIndex = array_search('erp', $uriSegments);
$recurso = isset($uriSegments[$erpIndex + 1]) ? $uriSegments[$erpIndex + 1] : null;
$id = isset($uriSegments[$erpIndex + 2]) ? $uriSegments[$erpIndex + 2] : null;

switch ($requestMethod) {
    case 'GET':
        if ($id) {
            $cliente = $modeloCliente->lerUm($id);
            if ($cliente) {
                echo json_encode($cliente);
            } else {
                http_response_code(404);
                echo json_encode(["mensagem" => "Cliente nao encontrado."]);
            }
        } else {
            $clientes = $modeloCliente->lerTodos();
            echo json_encode($clientes);
        }
        break;

    case 'POST':
        $dados = json_decode(file_get_contents("php://input"), true);
        $name = $dados['name'] ?? '';
        $email = $dados['email'] ?? '';
        $phone = $dados['phone'] ?? '';
        // CORREÇÃO AQUI: Pegar 'address' diretamente do frontend, pois já vem combinado
        $address = $dados['address'] ?? '';
        $cpf = $dados['cpf'] ?? '';
        $birth_date = $dados['birth_date'] ?? null;

        // REMOVIDO: O frontend já combina o endereço. Estas linhas não são mais necessárias aqui.
        // $street = $dados['street'] ?? '';
        // $number = $dados['number'] ?? '';
        // $neighborhood = $dados['neighborhood'] ?? '';
        // $city = $dados['city'] ?? '';
        // $state = $dados['state'] ?? '';
        // $address = trim(implode(', ', array_filter([$street, $number, $neighborhood, $city, $state])));

        if (empty($name) || empty($email)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nome e e-mail do cliente são obrigatórios."]);
            $logger->log(Logger::WARNING, "Tentativa de criar cliente com campos incompletos.");
            exit();
        }

        // Passando CPF e Birth Date para o ModeloCliente
        if ($modeloCliente->criar($name, $email, $phone, $address, $cpf, $birth_date)) {
            http_response_code(201);
            echo json_encode(["mensagem" => "Cliente criado com sucesso."]);
            $logger->log(Logger::INFO, "Cliente criado: " . $name);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Nao foi possivel criar o cliente."]);
            $logger->log(Logger::ERROR, "Falha ao criar cliente: " . $name);
        }
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "ID do cliente nao especificado para atualizacao."]);
            exit();
        }
        $dados = json_decode(file_get_contents("php://input"), true);
        $name = $dados['name'] ?? '';
        $email = $dados['email'] ?? '';
        $phone = $dados['phone'] ?? '';
        // CORREÇÃO AQUI: Pegar 'address' diretamente do frontend, pois já vem combinado
        $address = $dados['address'] ?? '';
        $cpf = $dados['cpf'] ?? '';
        $birth_date = $dados['birth_date'] ?? null;

        // REMOVIDO: O frontend já combina o endereço. Estas linhas não são mais necessárias aqui.
        // $street = $dados['street'] ?? '';
        // $number = $dados['number'] ?? '';
        // $neighborhood = $dados['neighborhood'] ?? '';
        // $city = $dados['city'] ?? '';
        // $state = $dados['state'] ?? '';
        // $address = trim(implode(', ', array_filter([$street, $number, $neighborhood, $city, $state])));

        if (empty($name) || empty($email)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nome e e-mail do cliente são obrigatórios."]);
            $logger->log(Logger::WARNING, "Tentativa de atualizar cliente ID {$id} com campos incompletos.");
            exit();
        }

        // Passando CPF e Birth Date para o ModeloCliente
        if ($modeloCliente->atualizar($id, $name, $email, $phone, $address, $cpf, $birth_date)) {
            echo json_encode(["mensagem" => "Cliente atualizado com sucesso."]);
            $logger->log(Logger::INFO, "Cliente ID {$id} atualizado: " . $name);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Nao foi possivel atualizar o cliente."]);
            $logger->log(Logger::ERROR, "Falha ao atualizar cliente ID {$id}: " . $name);
        }
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "ID do cliente nao especificado para exclusao."]);
            exit();
        }

        if ($modeloCliente->deletar($id)) {
            echo json_encode(["mensagem" => "Cliente excluido com sucesso."]);
            $logger->log(Logger::INFO, "Cliente ID {$id} excluido.");
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Nao foi possivel excluir o cliente."]);
            $logger->log(Logger::ERROR, "Falha ao excluir cliente ID {$id}.");
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["erro" => "Metodo nao permitido."]);
        break;
}
?>