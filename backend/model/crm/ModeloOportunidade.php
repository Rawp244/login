<?php
// backend/model/crm/ModeloOportunidade.php
require_once __DIR__ . '/../../config/Database.php';

class ModeloOportunidade {
    private $conexao;
    private $nome_tabela = "crm.opportunities";

    public function __construct() {
        $database = new Database();
        $this->conexao = $database->getConnection();
    }

    public function criar($titulo, $descricao, $data_inicio, $data_fim, $desconto, $modelo_veiculo) {
        $query = "INSERT INTO " . $this->nome_tabela . " (title, description, start_date, end_date, discount, vehicle_model) VALUES (:titulo, :descricao, :data_inicio, :data_fim, :desconto, :modelo_veiculo)";
        $stmt = $this->conexao->prepare($query);

        $titulo_limpo = htmlspecialchars(strip_tags($titulo));
        $descricao_limpa = htmlspecialchars(strip_tags($descricao));
        $data_inicio_limpa = htmlspecialchars(strip_tags($data_inicio));
        $data_fim_limpa = htmlspecialchars(strip_tags($data_fim));
        $desconto_limpo = htmlspecialchars(strip_tags($desconto));
        $modelo_veiculo_limpo = htmlspecialchars(strip_tags($modelo_veiculo));

        $stmt->bindParam(":titulo", $titulo_limpo);
        $stmt->bindParam(":descricao", $descricao_limpa);
        $stmt->bindParam(":data_inicio", $data_inicio_limpa);
        $stmt->bindParam(":data_fim", $data_fim_limpa);
        $stmt->bindParam(":desconto", $desconto_limpo);
        $stmt->bindParam(":modelo_veiculo", $modelo_veiculo_limpo);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function lerTodos() {
        $query = "SELECT id, title, description, start_date, end_date, discount, vehicle_model, created_at FROM " . $this->nome_tabela . " ORDER BY created_at DESC";
        $stmt = $this->conexao->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lerUm($id) {
        $query = "SELECT id, title, description, start_date, end_date, discount, vehicle_model, created_at FROM " . $this->nome_tabela . " WHERE id = :id LIMIT 1";
        $stmt = $this->conexao->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $titulo, $descricao, $data_inicio, $data_fim, $desconto, $modelo_veiculo) {
        $query = "UPDATE " . $this->nome_tabela . " SET title = :titulo, description = :descricao, start_date = :data_inicio, end_date = :data_fim, discount = :desconto, vehicle_model = :modelo_veiculo WHERE id = :id";
        $stmt = $this->conexao->prepare($query);

        $titulo_limpo = htmlspecialchars(strip_tags($titulo));
        $descricao_limpa = htmlspecialchars(strip_tags($descricao));
        $data_inicio_limpa = htmlspecialchars(strip_tags($data_inicio));
        $data_fim_limpa = htmlspecialchars(strip_tags($data_fim));
        $desconto_limpo = htmlspecialchars(strip_tags($desconto));
        $modelo_veiculo_limpo = htmlspecialchars(strip_tags($modelo_veiculo));

        $stmt->bindParam(":titulo", $titulo_limpo);
        $stmt->bindParam(":descricao", $descricao_limpa);
        $stmt->bindParam(":data_inicio", $data_inicio_limpa);
        $stmt->bindParam(":data_fim", $data_fim_limpa);
        $stmt->bindParam(":desconto", $desconto_limpo);
        $stmt->bindParam(":modelo_veiculo", $modelo_veiculo_limpo);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function deletar($id) {
        $query = "DELETE FROM " . $this->nome_tabela . " WHERE id = :id";
        $stmt = $this->conexao->prepare($query);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>