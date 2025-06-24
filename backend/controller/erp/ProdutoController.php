<?php
// backend/controller/erp/ProdutoController.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../model/erp/ModeloProduto.php';
require_once __DIR__ . '/../../utils/Logger.php';

// --- GARANTA QUE ESSAS LINHAS ESTEJAM NO INÍCIO DO ARQUIVO ---
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permite requisições de qualquer origem (seu frontend localhost:3000)
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE'); // Métodos permitidos
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Cabeçalhos permitidos
// --- FIM DOS CABEÇALHOS CORS ---

$produtoModelo = new ModeloProduto();
$logger = new Logger();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$requestMethod = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriSegments = explode('/', trim($uri, '/'));

// Encontrar o índice de 'erp' na URI
$erpIndex = array_search('erp', $uriSegments);
// O recurso (ex: 'produtos') seria o próximo segmento
$recurso = isset($uriSegments[$erpIndex + 1]) ? $uriSegments[$erpIndex + 1] : null;
// O ID do recurso, se houver
$id = isset($uriSegments[$erpIndex + 2]) ? $uriSegments[$erpIndex + 2] : null;


switch ($requestMethod) {
    case 'GET':
        if ($id) {
            $produto = $produtoModelo->lerUm($id);
            if ($produto) {
                echo json_encode($produto);
            } else {
                http_response_code(404);
                echo json_encode(["mensagem" => "Produto nao encontrado."]);
            }
        } else {
            $produtos = $produtoModelo->lerTodos();
            echo json_encode($produtos);
        }
        break;

    case 'POST':
        $dados = json_decode(file_get_contents("php://input"), true);
        $nome = $dados['name'] ?? '';
        $descricao = $dados['description'] ?? '';
        $preco = $dados['price'] ?? 0;
        $sku = $dados['sku'] ?? '';

        if (empty($nome) || empty($preco)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nome e preco do produto sao obrigatorios."]);
            $logger->log(Logger::WARNING, "Tentativa de criar produto com campos incompletos.");
            exit();
        }

        if ($produtoModelo->criar($nome, $descricao, $preco, $sku)) {
            http_response_code(201); // Created
            echo json_encode(["mensagem" => "Produto criado com sucesso."]);
            $logger->log(Logger::INFO, "Produto criado: " . $nome);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(["erro" => "Nao foi possivel criar o produto."]);
            $logger->log(Logger::ERROR, "Falha ao criar produto: " . $nome);
        }
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "ID do produto nao especificado para atualizacao."]);
            exit();
        }
        $dados = json_decode(file_get_contents("php://input"), true);
        $nome = $dados['name'] ?? '';
        $descricao = $dados['description'] ?? '';
        $preco = $dados['price'] ?? 0;
        $sku = $dados['sku'] ?? '';

        if (empty($nome) || empty($preco)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nome e preco do produto sao obrigatorios."]);
            $logger->log(Logger::WARNING, "Tentativa de atualizar produto ID {$id} com campos incompletos.");
            exit();
        }

        if ($produtoModelo->atualizar($id, $nome, $descricao, $preco, $sku)) {
            echo json_encode(["mensagem" => "Produto atualizado com sucesso."]);
            $logger->log(Logger::INFO, "Produto ID {$id} atualizado: " . $nome);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Nao foi possivel atualizar o produto."]);
            $logger->log(Logger::ERROR, "Falha ao atualizar produto ID {$id}: " . $nome);
        }
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "ID do produto nao especificado para exclusao."]);
            exit();
        }

        if ($produtoModelo->deletar($id)) {
            echo json_encode(["mensagem" => "Produto excluido com sucesso."]);
            $logger->log(Logger::INFO, "Produto ID {$id} excluido.");
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Nao foi possivel excluir o produto."]);
            $logger->log(Logger::ERROR, "Falha ao excluir produto ID {$id}.");
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["erro" => "Metodo nao permitido."]);
        break;
}
?>