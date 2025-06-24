<?php
// backend/controller/erp/ConcessionariaVWController.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Caminho para o ModeloConcessionariaVW.php
// De backend/controller/erp/ para backend/model/erp/: sobe 2 níveis (../../) e entra em model/erp/
require_once __DIR__ . '/../../model/erp/ModeloConcessionariaVW.php';

// Caminho para o Logger.php
// De backend/controller/erp/ para backend/utils/: sobe 2 níveis (../../) e entra em utils/
require_once __DIR__ . '/../../utils/Logger.php';

// Caminho para o autoload.php (que está na raiz do projeto 'loginmvc')
// De backend/controller/erp/ para loginmvc/: sobe 3 níveis (../../../) e entra em vendor/
require_once __DIR__ . '/../../../vendor/autoload.php';

// Caminho para o Auth.php
// De backend/controller/erp/ para backend/utils/: sobe 2 níveis (../../) e entra em utils/
require_once __DIR__ . '/../../utils/Auth.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS'); // Apenas GET e OPTIONS para este endpoint
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$modeloConcessionaria = new ModeloConcessionariaVW();
$logger = new Logger();

// Lidar com requisições OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Validação do Token JWT
$userData = Auth::validateToken();
if (!$userData) {
    http_response_code(401); // Unauthorized
    echo json_encode(["erro" => "Acesso não autorizado. Token inválido ou ausente."]);
    exit();
}

$requestMethod = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriSegments = explode('/', trim($uri, '/'));

$erpIndex = array_search('erp', $uriSegments);
$recurso = isset($uriSegments[$erpIndex + 1]) ? $uriSegments[$erpIndex + 1] : null;

switch ($requestMethod) {
    case 'GET':
        $concessionarias = $modeloConcessionaria->lerTodos();
        echo json_encode($concessionarias);
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["erro" => "Método não permitido."]);
        break;
}
?>