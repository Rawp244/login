<?php

// Correção: Caminho para Database.php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/Logger.php'; // Este já estava correto

class ModeloEstoque {
    private $conn;
    private $logger;
    private $table_name = "stock_movements";
    private $table_products = "products";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->logger = new Logger();
    }

    public function criarMovimento($product_id, $quantity, $type) {
        try {
            $this->conn->beginTransaction();

            // 1. Inserir a movimentação de estoque
            $query = "INSERT INTO erp." . $this->table_name . " (product_id, quantity, type, movement_date) VALUES (:product_id, :quantity, :type, NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":product_id", $product_id);
            $stmt->bindParam(":quantity", $quantity);
            $stmt->bindParam(":type", $type);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao inserir movimentação de estoque.");
            }

            // 2. Atualizar o estoque na tabela de produtos
            $query_update_stock = "";
            if ($type === 'entrada') {
                $query_update_stock = "UPDATE erp." . $this->table_products . " SET stock = stock + :quantity WHERE id = :product_id";
            } elseif ($type === 'saida') {
                // Verificar se há estoque suficiente antes de remover
                $stmt_check_stock = $this->conn->prepare("SELECT stock FROM erp." . $this->table_products . " WHERE id = :product_id");
                $stmt_check_stock->bindParam(":product_id", $product_id);
                $stmt_check_stock->execute();
                $current_stock = $stmt_check_stock->fetch(PDO::FETCH_ASSOC)['stock'];

                if ($current_stock < $quantity) {
                    throw new Exception("Estoque insuficiente para registrar a saída.");
                }
                $query_update_stock = "UPDATE erp." . $this->table_products . " SET stock = stock - :quantity WHERE id = :product_id";
            } else {
                throw new Exception("Tipo de movimento de estoque inválido.");
            }

            $stmt_update = $this->conn->prepare($query_update_stock);
            $stmt_update->bindParam(":quantity", $quantity);
            $stmt_update->bindParam(":product_id", $product_id);

            if (!$stmt_update->execute()) {
                throw new Exception("Erro ao atualizar o estoque do produto.");
            }

            $this->conn->commit();
            $this->logger->log(Logger::INFO, "Movimento de estoque registrado para produto ID " . $product_id . " (Tipo: " . $type . ", Quantidade: " . $quantity . ")");
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->logger->log(Logger::ERROR, "Erro de PDO ao criar movimento de estoque: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->logger->log(Logger::ERROR, "Erro ao criar movimento de estoque: " . $e->getMessage());
            return false;
        }
    }

    public function lerTodosMovimentos() {
        $query = "SELECT sm.id, sm.product_id, p.name AS product_name, sm.quantity, sm.type, sm.movement_date
                  FROM erp." . $this->table_name . " sm
                  JOIN erp." . $this->table_products . " p ON sm.product_id = p.id
                  ORDER BY sm.movement_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>