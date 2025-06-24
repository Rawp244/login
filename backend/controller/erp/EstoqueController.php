<?php
// backend/controller/erp/EstoqueController.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- CABEÇALHOS CORS DEVEM ESTAR AQUI NO INÍCIO ---
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
// --- FIM DOS CABEÇALHOS CORS ---

require_once __DIR__ . '/../../model/erp/ModeloEstoque.php';
require_once __DIR__ . '/../../utils/Logger.php';

$modeloEstoque = new ModeloEstoque();
$logger = new Logger();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$requestMethod = $_SERVER["REQUEST_METHOD"];
// A rota para estoque não espera um ID, apenas operações de listar ou adicionar
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriSegments = explode('/', trim($uri, '/'));
$erpIndex = array_search('erp', $uriSegments);
// Verifique se o próximo segmento existe e é 'estoque'
$recurso = isset($uriSegments[$erpIndex + 1]) ? $uriSegments[$erpIndex + 1] : null;

// Não precisamos de $id para o estoque pois são apenas movimentos ou lista
$id = null; // Garante que $id seja nulo

switch ($requestMethod) {
    case 'GET':
        // Retorna o histórico de todas as movimentações de estoque
        $movimentos = $modeloEstoque->lerTodosMovimentos();
        echo json_encode($movimentos);
        break;

    case 'POST':
        $dados = json_decode(file_get_contents("php://input"), true);
        $product_id = $dados['product_id'] ?? null;
        $quantity = $dados['quantity'] ?? null;
        $type = $dados['type'] ?? ''; // 'entrada' ou 'saida'

        // Validação dos dados
        if (empty($product_id) || empty($quantity) || !is_numeric($quantity) || $quantity <= 0 || !in_array($type, ['entrada', 'saida'])) {
            http_response_code(400);
            echo json_encode(["erro" => "Dados invalidos. product_id, quantity (maior que 0) e type (entrada/saida) sao obrigatorios."]);
            $logger->log(Logger::WARNING, "Tentativa de criar movimento de estoque com dados incompletos ou invalidos.");
            exit();
        }

        // Garante que a quantidade seja um inteiro
        $quantity = (int)$quantity;

        if ($modeloEstoque->criarMovimento($product_id, $quantity, $type)) {
            http_response_code(201);
            echo json_encode(["mensagem" => "Movimento de estoque registrado com sucesso."]);
            $logger->log(Logger::INFO, "Movimento de estoque registrado para produto ID {$product_id}, Quantidade: {$quantity}, Tipo: {$type}.");
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Nao foi possivel registrar o movimento de estoque."]);
            $logger->log(Logger::ERROR, "Falha ao registrar movimento de estoque para produto ID {$product_id}.");
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["erro" => "Metodo nao permitido."]);
        break;
}
?>