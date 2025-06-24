<?php
// backend/controller/erp/PedidoController.php
ini_set('display_errors', 0); // Desligue display_errors para evitar poluir a saída JSON!
error_reporting(E_ALL);

require_once __DIR__ . '/../../model/erp/ModeloPedido.php';
require_once __DIR__ . '/../../utils/Logger.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$modeloPedido = new ModeloPedido();
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
            $pedido = $modeloPedido->lerUmPedido($id);
            if ($pedido) {
                echo json_encode($pedido);
            } else {
                http_response_code(404);
                echo json_encode(["mensagem" => "Pedido nao encontrado."]);
            }
        } else {
            $pedidos = $modeloPedido->lerTodosPedidos();
            echo json_encode($pedidos);
        }
        break;

    case 'POST':
        $json_input = file_get_contents("php://input");
        $dados = json_decode($json_input, true);

        // --- NOVOS LOGS DETALHADOS PARA DEBUG NO CONTROLLER ---
        // Estes logs são temporários e podem ser removidos após a depuração
        $logger->log(Logger::INFO, "DEBUG CONTROLLER: JSON recebido (raw): " . $json_input);
        $logger->log(Logger::INFO, "DEBUG CONTROLLER: Dados decodificados (array): " . print_r($dados, true));
        $logger->log(Logger::INFO, "DEBUG CONTROLLER: Valor de \$dados['sale_type'] direto: " . ($dados['sale_type'] ?? 'CHAVE sale_type AUSENTE/NULL'));
        $logger->log(Logger::INFO, "DEBUG CONTROLLER: Valor de \$dados['status'] direto (buscando por 'status'): " . ($dados['status'] ?? 'CHAVE status AUSENTE/NULL'));
        // --- FIM DOS NOVOS LOGS DETALHADOS ---

        $client_id = $dados['client_id'] ?? null;
        $total_amount = $dados['total_amount'] ?? 0;
        // CORREÇÃO AQUI: Ler 'sale_type' do array $dados, que é o que o frontend envia
        $status = $dados['sale_type'] ?? 'Venda de Veículo Zero KM'; // Fallback para um valor padrão seguro
        $items = $dados['items'] ?? [];

        if (empty($client_id) || empty($items)) {
            http_response_code(400);
            echo json_encode(["erro" => "Cliente e pelo menos um item sao obrigatorios."]);
            $logger->log(Logger::WARNING, "Tentativa de criar pedido com campos incompletos.");
            exit();
        }

        try {
            if ($modeloPedido->criarPedido($client_id, $total_amount, $status, $items)) {
                http_response_code(201);
                echo json_encode(["mensagem" => "Pedido criado com sucesso."]);
                $logger->log(Logger::INFO, "Pedido criado para cliente ID: " . $client_id);
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode(["erro" => "Nao foi possivel criar o pedido. Verifique os logs do servidor para mais detalhes."]);
            }
        } catch (Exception $e) {
            http_response_code(400); // Bad Request ou algo mais específico como 409 Conflict
            echo json_encode(["erro" => $e->getMessage()]); // Retorna a mensagem exata da exceção, se for lançada
            $logger->log(Logger::ERROR, "Erro capturado no controller ao criar pedido: " . $e->getMessage());
        }
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "ID do pedido nao especificado para atualizacao."]);
            exit();
        }
        $json_input = file_get_contents("php://input"); // Adicionado para consistência de log
        $dados = json_decode($json_input, true);

        // --- NOVOS LOGS DETALHADOS PARA DEBUG NO CONTROLLER (PUT) ---
        // Estes logs são temporários e podem ser removidos após a depuração
        $logger->log(Logger::INFO, "DEBUG CONTROLLER (PUT): JSON recebido (raw): " . $json_input);
        $logger->log(Logger::INFO, "DEBUG CONTROLLER (PUT): Dados decodificados (array): " . print_r($dados, true));
        $logger->log(Logger::INFO, "DEBUG CONTROLLER (PUT): Valor de \$dados['sale_type'] direto: " . ($dados['sale_type'] ?? 'CHAVE sale_type AUSENTE/NULL'));
        $logger->log(Logger::INFO, "DEBUG CONTROLLER (PUT): Valor de \$dados['status'] direto (buscando por 'status'): " . ($dados['status'] ?? 'CHAVE status AUSENTE/NULL'));
        // --- FIM DOS NOVOS LOGS DETALHADOS ---

        $client_id = $dados['client_id'] ?? null;
        $total_amount = $dados['total_amount'] ?? 0;
        // CORREÇÃO AQUI: Ler 'sale_type' para o PUT também
        $status = $dados['sale_type'] ?? 'Venda de Veículo Zero KM'; // Fallback para um valor padrão seguro
        $items = $dados['items'] ?? [];

        if (empty($client_id) || empty($items)) {
            http_response_code(400);
            echo json_encode(["erro" => "Cliente e pelo menos um item sao obrigatorios para atualizacao."]);
            $logger->log(Logger::WARNING, "Tentativa de atualizar pedido ID {$id} com campos incompletos.");
            exit();
        }

        try {
            if ($modeloPedido->atualizarPedido($id, $client_id, $total_amount, $status, $items)) {
                echo json_encode(["mensagem" => "Pedido atualizado com sucesso."]);
                $logger->log(Logger::INFO, "Pedido ID {$id} atualizado para cliente ID: " . $client_id);
            } else {
                http_response_code(500);
                echo json_encode(["erro" => "Nao foi possivel atualizar o pedido. Verifique os logs do servidor para mais detalhes."]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["erro" => $e->getMessage()]);
            $logger->log(Logger::ERROR, "Erro capturado no controller ao atualizar pedido: " . $e->getMessage());
        }
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "ID do pedido nao especificado para exclusao."]);
            exit();
        }

        try {
            if ($modeloPedido->deletarPedido($id)) {
                echo json_encode(["mensagem" => "Pedido excluido com sucesso."]);
                $logger->log(Logger::INFO, "Pedido ID {$id} excluido.");
            } else {
                http_response_code(500);
                echo json_encode(["erro" => "Nao foi possivel excluir o pedido. Verifique os logs do servidor para mais detalhes."]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["erro" => $e->getMessage()]);
            $logger->log(Logger::ERROR, "Erro capturado no controller ao deletar pedido: " . $e->getMessage());
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["erro" => "Metodo nao permitido."]);
        break;
}
?>