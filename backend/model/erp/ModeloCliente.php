<?php
// backend/model/erp/ModeloCliente.php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/Logger.php';

class ModeloCliente {
    private $conn;
    private $logger;
    private $table_name = "crm.clients";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->logger = new Logger();
    }

    // Adicionado $cpf e $birth_date como parâmetros
    public function criar($name, $email, $phone, $address, $cpf = '', $birth_date = null) {
        $query = "INSERT INTO " . $this->table_name . " (name, email, phone, address, cpf, birth_date) VALUES (:name, :email, :phone, :address, :cpf, :birth_date)";
        $stmt = $this->conn->prepare($query);

        // Sanitização dos dados (boa prática de segurança)
        $name_sanitized = htmlspecialchars(strip_tags($name));
        $email_sanitized = htmlspecialchars(strip_tags($email));
        $phone_sanitized = htmlspecialchars(strip_tags($phone));
        $address_sanitized = htmlspecialchars(strip_tags($address));
        $cpf_sanitized = htmlspecialchars(strip_tags($cpf));
        // birth_date não precisa de strip_tags se for do tipo 'date' e já validado

        $stmt->bindParam(":name", $name_sanitized);
        $stmt->bindParam(":email", $email_sanitized);
        $stmt->bindParam(":phone", $phone_sanitized);
        $stmt->bindParam(":address", $address_sanitized);
        $stmt->bindParam(":cpf", $cpf_sanitized);
        $stmt->bindParam(":birth_date", $birth_date); // Data deve ser uma string no formato 'YYYY-MM-DD' ou null

        try {
            if ($stmt->execute()) {
                $this->logger->log(Logger::INFO, "Cliente criado: " . $name);
                return true;
            }
        } catch (PDOException $e) {
            $this->logger->log(Logger::ERROR, "Erro ao criar cliente: " . $e->getMessage());
        }
        return false;
    }

    public function lerTodos() {
        $query = "SELECT id, name, email, phone, address, cpf, birth_date, created_at FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lerUm($id) {
        $query = "SELECT id, name, email, phone, address, cpf, birth_date, created_at FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Adicionado $cpf e $birth_date como parâmetros
    public function atualizar($id, $name, $email, $phone, $address, $cpf = '', $birth_date = null) {
        $query = "UPDATE " . $this->table_name . " SET name = :name, email = :email, phone = :phone, address = :address, cpf = :cpf, birth_date = :birth_date WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitização dos dados (boa prática de segurança)
        $name_sanitized = htmlspecialchars(strip_tags($name));
        $email_sanitized = htmlspecialchars(strip_tags($email));
        $phone_sanitized = htmlspecialchars(strip_tags($phone));
        $address_sanitized = htmlspecialchars(strip_tags($address));
        $cpf_sanitized = htmlspecialchars(strip_tags($cpf));

        $stmt->bindParam(":name", $name_sanitized);
        $stmt->bindParam(":email", $email_sanitized);
        $stmt->bindParam(":phone", $phone_sanitized);
        $stmt->bindParam(":address", $address_sanitized);
        $stmt->bindParam(":cpf", $cpf_sanitized);
        $stmt->bindParam(":birth_date", $birth_date); // Data deve ser uma string no formato 'YYYY-MM-DD' ou null
        $stmt->bindParam(":id", $id);

        try {
            if ($stmt->execute()) {
                $this->logger->log(Logger::INFO, "Cliente ID {$id} atualizado: " . $name);
                return true;
            }
        } catch (PDOException $e) {
            $this->logger->log(Logger::ERROR, "Erro ao atualizar cliente ID {$id}: " . $e->getMessage());
        }
        return false;
    }

    public function deletar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        try {
            if ($stmt->execute()) {
                $this->logger->log(Logger::INFO, "Cliente ID {$id} deletado.");
                return true;
            }
        } catch (PDOException $e) {
            $this->logger->log(Logger::ERROR, "Erro ao deletar cliente ID {$id}: " . $e->getMessage());
        }
        return false;
    }
}
?>