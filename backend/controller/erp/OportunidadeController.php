<?php
// backend/controller/erp/OportunidadeController.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../model/erp/ModeloOportunidade.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../utils/Auth.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Caminho correto
require_once __DIR__ . '/../../utils/EmailService.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$modeloOportunidade = new ModeloOportunidade();
$logger = new Logger();
$emailService = new EmailService();

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

$controllerBaseIndex = array_search('controller', $uriSegments);
$controllerFile = isset($uriSegments[$controllerBaseIndex + 1]) ? $uriSegments[$controllerBaseIndex + 1] : null;
$action = isset($uriSegments[$controllerBaseIndex + 2]) ? $uriSegments[$controllerBaseIndex + 2] : null;
$resourceId = isset($uriSegments[$controllerBaseIndex + 3]) ? $uriSegments[$controllerBaseIndex + 3] : null;

$userId = null;
$userProfile = null;
$userData = Auth::validateToken();

if ($userData) {
    $userId = $userData['user_id'] ?? null;
    $userProfile = $userData['profile'] ?? null;
}
// DEBUG CRÍTICO: Loga o userId e userProfile obtidos do token
$logger->log(Logger::INFO, "DEBUG CONTROLLER: userId obtido do token: " . ($userId ?? 'NULL') . ", userProfile: " . ($userProfile ?? 'NULL'));


switch ($requestMethod) {
    case 'GET':
        if (!$userProfile || ($userProfile !== 'admin' && !(isset($_GET['promocoes_gerais']) && $_GET['promocoes_gerais'] == 'true'))) {
            http_response_code(403);
            echo json_encode(["erro" => "Acesso negado. Você não tem permissão para listar promoções."]);
            exit();
        }

        if ($id) {
            $oportunidade = $modeloOportunidade->lerUm($id);
            if ($oportunidade) {
                echo json_encode($oportunidade);
            } else {
                http_response_code(404);
                echo json_encode(["mensagem" => "Promoção não encontrada."]);
            }
        } else {
            if (isset($_GET['promocoes_gerais']) && $_GET['promocoes_gerais'] == 'true') {
                 $oportunidades = $modeloOportunidade->lerOportunidadesAtivasGerais();
            } else {
                 $oportunidades = $modeloOportunidade->lerTodos();
            }
            echo json_encode($oportunidades);
        }
        break;

    case 'POST':
        if (!$userProfile || $userProfile !== 'admin') {
            http_response_code(403);
            echo json_encode(["erro" => "Acesso negado. Apenas administradores podem criar promoções."]);
            exit();
        }

        $dados = json_decode(file_get_contents("php://input"), true);
        
        $titulo = $dados['titulo'] ?? '';
        $descricao = $dados['descricao'] ?? '';
        $data_inicio = $dados['data_inicio'] ?? '';
        $data_fim = $dados['data_fim'] ?? null;
        $valor_associado = $dados['valor_associado'] ?? null;
        $status = $dados['status'] ?? 'ativa';
        
        $tipo = 'promocao';
        $id_cliente = null; 
        $criado_por_usuario_id = $userId; // O ID que vem do token

        // DEBUG CRÍTICO: Loga o userId que será usado na criação
        $logger->log(Logger::INFO, "DEBUG CONTROLLER: userId que será passado para ModeloOportunidade->criar(): " . ($criado_por_usuario_id ?? 'NULL'));

        if (empty($titulo) || empty($data_inicio)) {
            http_response_code(400);
            echo json_encode(["erro" => "Título e Data de Início são obrigatórios para a Promoção."]);
            $logger->log(Logger::WARNING, "Tentativa de criar Promoção com campos incompletos.");
            exit();
        }

        if ($modeloOportunidade->criar($titulo, $descricao, $tipo, $data_inicio, $data_fim, $valor_associado, $status, $id_cliente, $criado_por_usuario_id)) {
            http_response_code(201);
            echo json_encode(["mensagem" => "Promoção criada com sucesso."]);
            $logger->log(Logger::INFO, "Promoção criada: " . $titulo);

            $promocaoCompleta = [
                'titulo' => $titulo,
                'descricao' => $descricao,
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim,
                'valor_associado' => $valor_associado,
                'status' => $status
            ];
            $emailService->enviarPromocaoParaTodosClientes($promocaoCompleta);

        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Não foi possível criar a Promoção."]);
            $logger->log(Logger::ERROR, "Falha ao criar Promoção: " . $titulo);
        }
        break;

    case 'PUT':
        if (!$userProfile || $userProfile !== 'admin') {
            http_response_code(403);
            echo json_encode(["erro" => "Acesso negado. Apenas administradores podem atualizar promoções."]);
            exit();
        }

        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "ID da Promoção não especificado para atualização."]);
            exit();
        }
        $dados = json_decode(file_get_contents("php://input"), true);
        
        $titulo = $dados['titulo'] ?? '';
        $descricao = $dados['descricao'] ?? '';
        $data_inicio = $dados['data_inicio'] ?? '';
        $data_fim = $dados['data_fim'] ?? null;
        $valor_associado = $dados['valor_associado'] ?? null;
        $status = $dados['status'] ?? 'ativa';
        
        $tipo = 'promocao';
        $id_cliente = null;

        $oportunidadeExistente = $modeloOportunidade->lerUm($id);
        $criado_por_usuario_id = $oportunidadeExistente['criado_por_usuario_id'] ?? $userId;

        if (empty($titulo) || empty($data_inicio)) {
            http_response_code(400);
            echo json_encode(["erro" => "Título e Data de Início são obrigatórios para a Promoção."]);
            $logger->log(Logger::WARNING, "Tentativa de atualizar Promoção ID {$id} com campos incompletos.");
            exit();
        }
        
        if ($modeloOportunidade->atualizar($id, $titulo, $descricao, $tipo, $data_inicio, $data_fim, $valor_associado, $status, $id_cliente, $criado_por_usuario_id)) {
            echo json_encode(["mensagem" => "Promoção atualizada com sucesso."]);
            $logger->log(Logger::INFO, "Promoção ID {$id} atualizada: " . $titulo);
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Não foi possível atualizar a Promoção."]);
            $logger->log(Logger::ERROR, "Falha ao atualizar Promoção ID {$id}: " . $titulo);
        }
        break;

    case 'DELETE':
        if (!$userProfile || $userProfile !== 'admin') {
            http_response_code(403);
            echo json_encode(["erro" => "Acesso negado. Apenas administradores podem excluir promoções."]);
            exit();
        }

        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "ID da Promoção não especificado para exclusão."]);
            exit();
        }

        if ($modeloOportunidade->deletar($id)) {
            echo json_encode(["mensagem" => "Promoção excluída com sucesso."]);
            $logger->log(Logger::INFO, "Promoção ID {$id} excluída.");
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Não foi possível excluir a Promoção."]);
            $logger->log(Logger::ERROR, "Falha ao excluir Promoção ID {$id}.");
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["erro" => "Método não permitido."]);
        break;
}
?>
