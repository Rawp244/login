<?php
// backend/model/erp/ModeloPedido.php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/Logger.php';

class ModeloPedido {
    private $conn;
    private $logger;
    private $table_orders = "erp.orders";
    private $table_order_items = "erp.order_items";
    private $table_products = "erp.products";
    private $table_clients = "crm.clients";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->logger = new Logger();
    }

    public function criarPedido($client_id, $total_amount, $status, $items) {
        try {
            $this->conn->beginTransaction();

            // Sanitização dos dados principais do pedido
            // Para IDs e valores numéricos, converter para o tipo correto é mais seguro que htmlspecialchars/strip_tags
            $client_id_sanitized = (int) $client_id;
            $total_amount_sanitized = (float) $total_amount;
            $status_sanitized = htmlspecialchars(strip_tags($status));

            // 1. Inserir o pedido principal
            $query_order = "INSERT INTO " . $this->table_orders . " (client_id, total_amount, status, order_date) VALUES (:client_id, :total_amount, :status, NOW())";
            $stmt_order = $this->conn->prepare($query_order);
            $stmt_order->bindParam(":client_id", $client_id_sanitized);
            $stmt_order->bindParam(":total_amount", $total_amount_sanitized);
            $stmt_order->bindParam(":status", $status_sanitized);

            // --- LOGS TEMPORÁRIOS PARA DEBUG (Adicionados aqui) ---
            $this->logger->log(Logger::INFO, "DEBUG: Tentando criar pedido.");
            $this->logger->log(Logger::INFO, "DEBUG: Query de Insercao de Pedido: " . $query_order);
            $this->logger->log(Logger::INFO, "DEBUG: client_id sendo usado: " . $client_id_sanitized);
            $this->logger->log(Logger::INFO, "DEBUG: total_amount sendo usado: " . $total_amount_sanitized);
            $this->logger->log(Logger::INFO, "DEBUG: status sendo usado: " . $status_sanitized);
            // --- FIM DOS LOGS TEMPORÁRIOS ---

            if (!$stmt_order->execute()) {
                throw new Exception("Erro ao criar o pedido principal.");
            }
            $order_id = $this->conn->lastInsertId();

            // 2. Inserir os itens do pedido e atualizar o estoque
            foreach ($items as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $price_at_order = $item['price_at_order'];

                // Sanitização dos dados do item - Convertendo para o tipo numérico adequado
                $product_id_sanitized = (int) $product_id;
                $quantity_sanitized = (int) $quantity;
                $price_at_order_sanitized = (float) $price_at_order;


                if (empty($product_id_sanitized) || !is_numeric($product_id_sanitized) || empty($quantity_sanitized) || !is_numeric($quantity_sanitized) || $quantity_sanitized <= 0 || empty($price_at_order_sanitized) || !is_numeric($price_at_order_sanitized)) {
                    throw new Exception("Dados de item de pedido inválidos. product_id, quantity e price_at_order são obrigatórios e devem ser números positivos.");
                }

                // Verificar estoque antes de inserir o item e dar baixa
                $stmt_check_stock = $this->conn->prepare("SELECT stock FROM " . $this->table_products . " WHERE id = :product_id FOR UPDATE");
                $stmt_check_stock->bindParam(":product_id", $product_id_sanitized);
                $stmt_check_stock->execute();
                $current_stock = $stmt_check_stock->fetch(PDO::FETCH_ASSOC)['stock'];

                if ($current_stock < $quantity_sanitized) {
                    $stmt_product_name = $this->conn->prepare("SELECT name FROM " . $this->table_products . " WHERE id = :product_id");
                    $stmt_product_name->bindParam(":product_id", $product_id_sanitized);
                    $stmt_product_name->execute();
                    $product_name = $stmt_product_name->fetchColumn() ?: 'Produto Desconhecido';

                    throw new Exception("Estoque insuficiente para o produto '" . $product_name . "'. Estoque atual: " . $current_stock . ", solicitado: " . $quantity_sanitized);
                }

                // Inserir item do pedido
                $query_item = "INSERT INTO " . $this->table_order_items . " (order_id, product_id, quantity, price_at_order) VALUES (:order_id, :product_id, :quantity, :price_at_order)";
                $stmt_item = $this->conn->prepare($query_item);
                $stmt_item->bindParam(":order_id", $order_id);
                $stmt_item->bindParam(":product_id", $product_id_sanitized);
                $stmt_item->bindParam(":quantity", $quantity_sanitized);
                $stmt_item->bindParam(":price_at_order", $price_at_order_sanitized);

                if (!$stmt_item->execute()) {
                    throw new Exception("Erro ao adicionar item ao pedido.");
                }

                // Atualizar estoque (saída)
                $query_update_stock = "UPDATE " . $this->table_products . " SET stock = stock - :quantity WHERE id = :product_id";
                $stmt_update_stock = $this->conn->prepare($query_update_stock);
                $stmt_update_stock->bindParam(":quantity", $quantity_sanitized);
                $stmt_update_stock->bindParam(":product_id", $product_id_sanitized);
                if (!$stmt_update_stock->execute()) {
                    throw new Exception("Erro ao dar baixa no estoque do produto ID " . $product_id . ".");
                }
            }

            $this->conn->commit();
            $this->logger->log(Logger::INFO, "Pedido criado com sucesso para cliente ID: " . $client_id . " (ID Pedido: " . $order_id . ")");
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->logger->log(Logger::ERROR, "Erro de PDO ao criar pedido: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->logger->log(Logger::ERROR, "Erro ao criar pedido (estoque/lógica): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    public function lerTodosPedidos() {
        $query = "SELECT o.id, o.client_id, c.name AS client_name, o.total_amount, o.status, o.order_date,
                         COALESCE(JSON_AGG(JSON_BUILD_OBJECT('product_id', oi.product_id, 'quantity', oi.quantity, 'price_at_order', oi.price_at_order)) FILTER (WHERE oi.id IS NOT NULL), '[]') AS items
                  FROM " . $this->table_orders . " o
                  JOIN " . $this->table_clients . " c ON o.client_id = c.id
                  LEFT JOIN " . $this->table_order_items . " oi ON o.id = oi.order_id
                  GROUP BY o.id, o.client_id, c.name, o.total_amount, o.status, o.order_date
                  ORDER BY o.order_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pedidos as &$pedido) {
            if ($pedido['items']) {
                $pedido['items'] = json_decode($pedido['items'], true);
            } else {
                $pedido['items'] = [];
            }
        }
        return $pedidos;
    }

    public function lerUmPedido($id) {
         $query = "SELECT o.id, o.client_id, c.name AS client_name, o.total_amount, o.status, o.order_date,
                          COALESCE(JSON_AGG(JSON_BUILD_OBJECT('product_id', oi.product_id, 'quantity', oi.quantity, 'price_at_order', oi.price_at_order)) FILTER (WHERE oi.id IS NOT NULL), '[]') AS items
                   FROM " . $this->table_orders . " o
                   JOIN " . $this->table_clients . " c ON o.client_id = c.id
                   LEFT JOIN " . $this->table_order_items . " oi ON o.id = oi.order_id
                   WHERE o.id = :id
                   GROUP BY o.id, o.client_id, c.name, o.total_amount, o.status, o.order_date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pedido && $pedido['items']) {
            $pedido['items'] = json_decode($pedido['items'], true);
        } elseif ($pedido) {
            $pedido['items'] = [];
        }
        return $pedido;
    }

    public function atualizarPedido($id, $client_id, $total_amount, $status, $items) {
        try {
            $this->conn->beginTransaction();

            // Sanitização dos dados principais do pedido
            $client_id_sanitized = (int) $client_id;
            $total_amount_sanitized = (float) $total_amount;
            $status_sanitized = htmlspecialchars(strip_tags($status));


            // 1. Reverter o estoque dos itens antigos ANTES de deletá-los
            $current_items_query = "SELECT product_id, quantity FROM " . $this->table_order_items . " WHERE order_id = :order_id";
            $stmt_current_items = $this->conn->prepare($current_items_query);
            $stmt_current_items->bindParam(":order_id", $id);
            $stmt_current_items->execute();
            $current_items = $stmt_current_items->fetchAll(PDO::FETCH_ASSOC);

            foreach ($current_items as $item) {
                // Sanitização do item a reverter
                $item_product_id_sanitized = (int) $item['product_id'];
                $item_quantity_sanitized = (int) $item['quantity'];

                $query_revert_stock = "UPDATE " . $this->table_products . " SET stock = stock + :quantity WHERE id = :product_id";
                $stmt_revert_stock = $this->conn->prepare($query_revert_stock);
                $stmt_revert_stock->bindParam(":quantity", $item_quantity_sanitized);
                $stmt_revert_stock->bindParam(":product_id", $item_product_id_sanitized);
                if (!$stmt_revert_stock->execute()) {
                    throw new Exception("Erro ao reverter estoque para produto ID " . $item_product_id_sanitized . " durante atualização.");
                }
            }

            // 2. Deletar itens antigos do pedido
            $query_delete_items = "DELETE FROM " . $this->table_order_items . " WHERE order_id = :order_id";
            $stmt_delete_items = $this->conn->prepare($query_delete_items);
            $stmt_delete_items->bindParam(":order_id", $id);
            $stmt_delete_items->execute();

            // 3. Atualizar o pedido principal
            $query_order = "UPDATE " . $this->table_orders . " SET client_id = :client_id, total_amount = :total_amount, status = :status WHERE id = :id";
            $stmt_order = $this->conn->prepare($query_order);
            $stmt_order->bindParam(":client_id", $client_id_sanitized);
            $stmt_order->bindParam(":total_amount", $total_amount_sanitized);
            $stmt_order->bindParam(":status", $status_sanitized);
            $stmt_order->bindParam(":id", $id);
            if (!$stmt_order->execute()) {
                throw new Exception("Erro ao atualizar o pedido principal.");
            }

            // 4. Inserir os novos itens do pedido e dar baixa no estoque
            foreach ($items as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $price_at_order = $item['price_at_order'];

                // Sanitização dos dados do item
                $product_id_sanitized = (int) $product_id;
                $quantity_sanitized = (int) $quantity;
                $price_at_order_sanitized = (float) $price_at_order;

                if (empty($product_id_sanitized) || !is_numeric($product_id_sanitized) || empty($quantity_sanitized) || !is_numeric($quantity_sanitized) || $quantity_sanitized <= 0 || empty($price_at_order_sanitized) || !is_numeric($price_at_order_sanitized)) {
                    throw new Exception("Dados de item de pedido inválidos durante a atualização. product_id, quantity e price_at_order são obrigatórios e devem ser números positivos.");
                }

                // Verificar estoque antes de dar baixa para novos itens
                $stmt_check_stock = $this->conn->prepare("SELECT stock FROM " . $this->table_products . " WHERE id = :product_id FOR UPDATE");
                $stmt_check_stock->bindParam(":product_id", $product_id_sanitized);
                $stmt_check_stock->execute();
                $current_stock = $stmt_check_stock->fetch(PDO::FETCH_ASSOC)['stock'];

                if ($current_stock < $quantity_sanitized) {
                    $stmt_product_name = $this->conn->prepare("SELECT name FROM " . $this->table_products . " WHERE id = :product_id");
                    $stmt_product_name->bindParam(":product_id", $product_id_sanitized);
                    $stmt_product_name->execute();
                    $product_name = $stmt_product_name->fetchColumn() ?: 'Produto Desconhecido';
                    throw new Exception("Estoque insuficiente para o produto '" . $product_name . "' durante a atualização. Estoque atual: " . $current_stock . ", solicitado: " . $quantity_sanitized);
                }

                // Inserir item do pedido
                $query_item = "INSERT INTO " . $this->table_order_items . " (order_id, product_id, quantity, price_at_order) VALUES (:order_id, :product_id, :quantity, :price_at_order)";
                $stmt_item = $this->conn->prepare($query_item);
                $stmt_item->bindParam(":order_id", $id);
                $stmt_item->bindParam(":product_id", $product_id_sanitized);
                $stmt_item->bindParam(":quantity", $quantity_sanitized);
                $stmt_item->bindParam(":price_at_order", $price_at_order_sanitized);

                if (!$stmt_item->execute()) {
                    throw new Exception("Erro ao adicionar item ao pedido durante atualização.");
                }

                // Dar baixa no estoque para os novos itens
                $query_update_stock = "UPDATE " . $this->table_products . " SET stock = stock - :quantity WHERE id = :product_id";
                $stmt_update_stock = $this->conn->prepare($query_update_stock);
                $stmt_update_stock->bindParam(":quantity", $quantity_sanitized);
                $stmt_update_stock->bindParam(":product_id", $product_id_sanitized);
                if (!$stmt_update_stock->execute()) {
                    throw new Exception("Erro ao dar baixa no estoque do produto ID " . $product_id . " durante atualização.");
                }
            }

            $this->conn->commit();
            $this->logger->log(Logger::INFO, "Pedido atualizado com sucesso para cliente ID: " . $client_id . " (ID Pedido: " . $id . ")");
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->logger->log(Logger::ERROR, "Erro de PDO ao atualizar pedido: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->logger->log(Logger::ERROR, "Erro ao atualizar pedido (estoque/lógica): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    public function deletarPedido($id) {
        try {
            $this->conn->beginTransaction();

            // 1. Reverter o estoque dos itens do pedido antes de deletá-los
            $query_items_to_revert = "SELECT product_id, quantity FROM " . $this->table_order_items . " WHERE order_id = :order_id";
            $stmt_items_to_revert = $this->conn->prepare($query_items_to_revert);
            $stmt_items_to_revert->bindParam(":order_id", $id);
            $stmt_items_to_revert->execute();
            $items_to_revert = $stmt_items_to_revert->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items_to_revert as $item) {
                $query_revert_stock = "UPDATE " . $this->table_products . " SET stock = stock + :quantity WHERE id = :product_id";
                $stmt_revert_stock = $this->conn->prepare($query_revert_stock);
                $stmt_revert_stock->bindParam(":quantity", $item['quantity']);
                $stmt_revert_stock->bindParam(":product_id", $item['product_id']);
                if (!$stmt_revert_stock->execute()) {
                    throw new Exception("Erro ao reverter estoque para produto ID " . $item['product_id'] . " durante exclusão do pedido.");
                }
            }

            // 2. Deletar itens do pedido
            $query_delete_items = "DELETE FROM " . $this->table_order_items . " WHERE order_id = :order_id";
            $stmt_delete_items = $this->conn->prepare($query_delete_items);
            $stmt_delete_items->bindParam(":order_id", $id);
            if (!$stmt_delete_items->execute()) {
                throw new Exception("Erro ao deletar itens do pedido.");
            }

            // 3. Deletar o pedido principal
            $query_delete_order = "DELETE FROM " . $this->table_orders . " WHERE id = :id";
            $stmt_delete_order = $this->conn->prepare($query_delete_order);
            $stmt_delete_order->bindParam(":id", $id);
            if (!$stmt_delete_order->execute()) {
                throw new Exception("Erro ao deletar pedido.");
            }

            $this->conn->commit();
            $this->logger->log(Logger::INFO, "Pedido ID " . $id . " deletado com sucesso.");
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->logger->log(Logger::ERROR, "Erro de PDO ao deletar pedido: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->logger->log(Logger::ERROR, "Erro ao deletar pedido (lógica): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return false;
        }
    }
}
?>