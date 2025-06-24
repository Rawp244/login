<?php
// backend/model/erp/ModeloConcessionariaVW.php
require_once __DIR__ . '/../../config/Database.php'; // Caminho de backend/model/erp para backend/config/
require_once __DIR__ . '/../../utils/Logger.php';    // Caminho de backend/model/erp para backend/utils/

class ModeloConcessionariaVW {
    private $conn;
    private $logger;
    private $table_name = "crm.concessionarias_vw";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->logger = new Logger();
    }

    // Método para ler todas as concessionárias
    public function lerTodos() {
        $query = "SELECT id, nome, endereco, cidade, estado, latitude, longitude, telefone, email, site FROM " . $this->table_name . " ORDER BY nome ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Se quiser implementar o CRUD completo para concessionárias no futuro, adicione aqui:
    // public function criar($nome, $endereco, $cidade, $estado, $latitude, $longitude, $telefone, $email, $site) { ... }
    // public function lerUm($id) { ... }
    // public function atualizar($id, $nome, $endereco, $cidade, $estado, $latitude, $longitude, $telefone, $email, $site) { ... }
    // public function deletar($id) { ... }
}
?>