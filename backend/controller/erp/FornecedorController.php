<?php
// backend/controller/erp/FornecedorController.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../model/erp/ModeloFornecedor.php';
require_once __DIR__ . '/../../utils/Logger.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$fornecedorModelo = new ModeloFornecedor();
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
            $fornecedor = $fornecedorModelo->lerUm($id);
            if ($fornecedor) {
                echo json_encode($fornecedor);
            } else {
                http_response_code(404);
                echo json_encode(["mensagem" => "Fornecedor nao encontrado."]);
            }
        } else {
            $fornecedores = $fornecedorModelo->lerTodos();
            echo json_encode($fornecedores);
        }
        break;

    case 'POST':
        $dados = json_decode(file_get_contents("php://input"), true);

        $nome = $dados['name'] ?? '';
        $pessoa_contato = $dados['contact_person'] ?? '';
        $telefone = $dados['phone'] ?? '';
        $email = $dados['email'] ?? '';
        
        // Novos campos de endereço vindos do frontend
        $street = $dados['street'] ?? '';
        $number = $dados['number'] ?? '';
        $neighborhood = $dados['neighborhood'] ?? '';
        $city = $dados['city'] ?? '';
        $state = $dados['state'] ?? '';

        // Combina os campos de endereço em uma única string para salvar no banco
        // Usa array_filter para remover campos vazios antes de combinar, evitando ", , ,"
        $endereco = trim(implode(', ', array_filter([$street, $number, $neighborhood, $city, $state])));

        if (empty($nome) || empty($email)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nome e email do fornecedor sao obrigatorios."]);
            $logger->log(Logger::WARNING, "Tentativa de criar fornecedor com campos incompletos.");
            exit();
        }

        if ($fornecedorModelo->criar($nome, $pessoa_contato, $telefone, $email, $endereco)) {
            http_response_code(201);
            echo json_encode(["mensagem" => "Fornecedor criado com sucesso."]);
            $logger->log(Logger::INFO, "Fornecedor criado: " . $nome);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Nao foi possivel criar o fornecedor."]);
            $logger->log(Logger::ERROR, "Falha ao criar fornecedor: " . $nome);
        }
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "ID do fornecedor nao especificado para atualizacao."]);
            exit();
        }
        $dados = json_decode(file_get_contents("php://input"), true);
        
        $nome = $dados['name'] ?? '';
        $pessoa_contato = $dados['contact_person'] ?? '';
        $telefone = $dados['phone'] ?? '';
        $email = $dados['email'] ?? '';
        
        // Novos campos de endereço vindos do frontend
        $street = $dados['street'] ?? '';
        $number = $dados['number'] ?? '';
        $neighborhood = $dados['neighborhood'] ?? '';
        $city = $dados['city'] ?? '';
        $state = $dados['state'] ?? '';

        // Combina os campos de endereço em uma única string para salvar no banco
        $endereco = trim(implode(', ', array_filter([$street, $number, $neighborhood, $city, $state])));

        if (empty($nome) || empty($email)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nome e email do fornecedor sao obrigatorios."]);
            $logger->log(Logger::WARNING, "Tentativa de atualizar fornecedor ID {$id} com campos incompletos.");
            exit();
        }

        if ($fornecedorModelo->atualizar($id, $nome, $pessoa_contato, $telefone, $email, $endereco)) {
            echo json_encode(["mensagem" => "Fornecedor atualizado com sucesso."]);
            $logger->log(Logger::INFO, "Fornecedor ID {$id} atualizado: " . $nome);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Nao foi possivel atualizar o fornecedor."]);
            $logger->log(Logger::ERROR, "Falha ao atualizar fornecedor ID {$id}: " . $nome);
        }
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "ID do fornecedor nao especificado para exclusao."]);
            exit();
        }

        if ($fornecedorModelo->deletar($id)) {
            echo json_encode(["mensagem" => "Fornecedor excluido com sucesso."]);
            $logger->log(Logger::INFO, "Fornecedor ID {$id} excluido.");
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Nao foi possivel excluir o fornecedor."]);
            $logger->log(Logger::ERROR, "Falha ao excluir fornecedor ID {$id}.");
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["erro" => "Metodo nao permitido."]);
        break;
}
?>