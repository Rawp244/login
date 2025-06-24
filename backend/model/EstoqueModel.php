<?php
// backend/model/EstoqueModel.php

require_once __DIR__ . '/../config/Database.php';

class EstoqueModel {
    private $conn;
    private $table_name = "erp.estoque";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllEstoque() {
        $query = "SELECT 
                    e.id, 
                    e.produto_id, 
                    p.nome as produto_nome, 
                    e.quantidade, 
                    e.preco_custo, 
                    e.preco_venda
                  FROM 
                    " . $this->table_name . " e
                  LEFT JOIN
                    erp.produtos p ON e.produto_id = p.id
                  ORDER BY 
                    e.id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstoqueById($id) {
        $query = "SELECT 
                    e.id, 
                    e.produto_id, 
                    p.nome as produto_nome, 
                    e.quantidade, 
                    e.preco_custo, 
                    e.preco_venda
                  FROM 
                    " . $this->table_name . " e
                  LEFT JOIN
                    erp.produtos p ON e.produto_id = p.id
                  WHERE 
                    e.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createEstoque($produto_id, $quantidade, $preco_custo, $preco_venda) {
        $query = "INSERT INTO " . $this->table_name . " (produto_id, quantidade, preco_custo, preco_venda) VALUES (:produto_id, :quantidade, :preco_custo, :preco_venda)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":produto_id", $produto_id);
        $stmt->bindParam(":quantidade", $quantidade);
        $stmt->bindParam(":preco_custo", $preco_custo);
        $stmt->bindParam(":preco_venda", $preco_venda);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateEstoque($id, $produto_id, $quantidade, $preco_custo, $preco_venda) {
        $query = "UPDATE " . $this->table_name . " 
                  SET 
                    produto_id = :produto_id, 
                    quantidade = :quantidade, 
                    preco_custo = :preco_custo,
                    preco_venda = :preco_venda
                  WHERE 
                    id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":produto_id", $produto_id);
        $stmt->bindParam(":quantidade", $quantidade);
        $stmt->bindParam(":preco_custo", $preco_custo);
        $stmt->bindParam(":preco_venda", $preco_venda);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function deleteEstoque($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}