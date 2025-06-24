<?php
// backend/model/erp/ModeloOportunidade.php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/Logger.php';

class ModeloOportunidade {
    private $conn;
    private $logger;
    private $table_name = "crm.oportunidades";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->logger = new Logger();
    }

    public function criar($titulo, $descricao, $tipo, $data_inicio, $data_fim, $valor_associado, $status, $id_cliente, $criado_por_usuario_id) {
        $query = "INSERT INTO " . $this->table_name . " (titulo, descricao, tipo, data_inicio, data_fim, valor_associado, status, id_cliente, criado_por_usuario_id) VALUES (:titulo, :descricao, :tipo, :data_inicio, :data_fim, :valor_associado, :status, :id_cliente, :criado_por_usuario_id)";
        $stmt = $this->conn->prepare($query);

        $titulo_sanitized = htmlspecialchars(strip_tags($titulo));
        $descricao_sanitized = htmlspecialchars(strip_tags($descricao));
        $tipo_sanitized = htmlspecialchars(strip_tags($tipo));
        $status_sanitized = htmlspecialchars(strip_tags($status));

        $valor_associado_cleaned = ($valor_associado === '' || $valor_associado === null) ? null : (float)$valor_associado;
        $data_fim_cleaned = ($data_fim === '') ? null : $data_fim;


        $stmt->bindParam(":titulo", $titulo_sanitized);
        $stmt->bindParam(":descricao", $descricao_sanitized);
        $stmt->bindParam(":tipo", $tipo_sanitized);
        $stmt->bindParam(":data_inicio", $data_inicio);
        $stmt->bindParam(":data_fim", $data_fim_cleaned);
        $stmt->bindParam(":valor_associado", $valor_associado_cleaned);
        $stmt->bindParam(":status", $status_sanitized);
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT); 
        $stmt->bindParam(":criado_por_usuario_id", $criado_por_usuario_id, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                $this->logger->log(Logger::INFO, "Oportunidade/Promoção criada: " . $titulo);
                return true;
            }
        } catch (PDOException $e) {
                $this->logger->log(Logger::ERROR, "Erro ao criar Oportunidade/Promoção: " . $e->getMessage());
            }
            return false;
        }

        public function lerTodos() {
            // CORREÇÃO FINAL AQUI: Adicionado a junção para garantir client_name e created_by_username
            $query = "SELECT o.id, o.titulo, o.descricao, o.tipo, o.data_inicio, o.data_fim, o.valor_associado, o.status, o.created_at, o.updated_at, 
                             c.name as client_name, 
                             u.username as created_by_username
                      FROM " . $this->table_name . " o
                      LEFT JOIN crm.clients c ON o.id_cliente = c.id
                      LEFT JOIN public.usuarios u ON o.criado_por_usuario_id = u.id 
                      ORDER BY o.data_inicio DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC); 

            // DEBUG: Adicionado log para ver o que está sendo retornado do banco de dados
            $this->logger->log(Logger::INFO, "DEBUG MODELO OPORTUNIDADE: Resultado de lerTodos() do DB: " . print_r($results, true));

            return $results; // Retorna os resultados
        }

        public function lerUm($id) {
            $query = "SELECT o.id, o.titulo, o.descricao, o.tipo, o.data_inicio, o.data_fim, o.valor_associado, o.status, o.created_at, o.updated_at, o.id_cliente, o.criado_por_usuario_id, c.name as client_name, u.username as created_by_username
                      FROM " . $this->table_name . " o
                      LEFT JOIN crm.clients c ON o.id_cliente = c.id
                      LEFT JOIN public.usuarios u ON o.criado_por_usuario_id = u.id
                      WHERE o.id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function atualizar($id, $titulo, $descricao, $tipo, $data_inicio, $data_fim, $valor_associado, $status, $id_cliente, $criado_por_usuario_id) {
            $query = "UPDATE " . $this->table_name . " SET titulo = :titulo, descricao = :descricao, tipo = :tipo, data_inicio = :data_inicio, data_fim = :data_fim, valor_associado = :valor_associado, status = :status, id_cliente = :id_cliente, criado_por_usuario_id = :criado_por_usuario_id WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            $titulo_sanitized = htmlspecialchars(strip_tags($titulo));
            $descricao_sanitized = htmlspecialchars(strip_tags($descricao));
            $tipo_sanitized = htmlspecialchars(strip_tags($tipo));
            $status_sanitized = htmlspecialchars(strip_tags($status));

            $valor_associado_cleaned = ($valor_associado === '' || $valor_associado === null) ? null : (float)$valor_associado;
            $data_fim_cleaned = ($data_fim === '') ? null : $data_fim;

            $stmt->bindParam(":titulo", $titulo_sanitized);
            $stmt->bindParam(":descricao", $descricao_sanitized);
            $stmt->bindParam(":tipo", $tipo_sanitized);
            $stmt->bindParam(":data_inicio", $data_inicio);
            $stmt->bindParam(":data_fim", $data_fim_cleaned);
            $stmt->bindParam(":valor_associado", $valor_associado_cleaned);
            $stmt->bindParam(":status", $status_sanitized);
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->bindParam(":criado_por_usuario_id", $criado_por_usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(":id", $id);

            try {
                if ($stmt->execute()) {
                    $this->logger->log(Logger::INFO, "Oportunidade/Promoção ID {$id} atualizada: " . $titulo);
                    return true;
                }
            } catch (PDOException $e) {
                $this->logger->log(Logger::ERROR, "Erro ao atualizar Oportunidade/Promoção ID {$id}: " . $e->getMessage());
            }
            return false;
        }

        public function deletar($id) {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);

            try {
                if ($stmt->execute()) {
                    $this->logger->log(Logger::INFO, "Oportunidade/Promoção ID {$id} deletada.");
                    return true;
                }
            } catch (PDOException $e) {
                $this->logger->log(Logger::ERROR, "Erro ao deletar Oportunidade/Promoção ID {$id}: " . $e->getMessage());
            }
            return false;
        }

        public function lerOportunidadesAtivasGerais() {
            $query = "SELECT id, titulo, descricao, tipo, data_inicio, data_fim, valor_associado, status
                      FROM " . $this->table_name . "
                      WHERE tipo = 'promocao' AND status = 'ativa' AND (data_fim IS NULL OR data_fim >= CURRENT_DATE)
                      ORDER BY data_inicio DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function lerOportunidadesPorCliente($id_cliente) {
            $query = "SELECT id, titulo, descricao, tipo, data_inicio, data_fim, valor_associado, status
                      FROM " . $this->table_name . "
                      WHERE id_cliente = :id_cliente AND status = 'ativa'
                      ORDER BY data_inicio DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    ?>
