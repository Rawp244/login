<?php
// backend/model/erp/ModeloProduto.php

// Correção: Caminho para Database.php
require_once __DIR__ . '/../../config/Database.php';
// Adicionar o Logger se você pretende usá-lo neste modelo, ou remover se não
// require_once __DIR__ . '/../../utils/Logger.php';

class ModeloProduto {
    private $conexao;
    private $nome_tabela = "erp.products";

    public function __construct() {
        $database = new Database();
        $this->conexao = $database->getConnection();
        // $this->logger = new Logger(); // Descomente se for usar Logger aqui
    }

    public function criar($nome, $descricao, $preco, $sku) {
        // Adicionada a coluna 'stock' com valor padrão 0 no INSERT
        $query = "INSERT INTO " . $this->nome_tabela . " (name, description, price, sku, stock) VALUES (:nome, :descricao, :preco, :sku, 0)";
        $stmt = $this->conexao->prepare($query);

        // Limpar dados (prevenir XSS, SQL Injection básico)
        $nome_limpo = htmlspecialchars(strip_tags($nome));
        $descricao_limpa = htmlspecialchars(strip_tags($descricao));
        $preco_limpo = htmlspecialchars(strip_tags($preco));
        $sku_limpo = htmlspecialchars(strip_tags($sku));

        $stmt->bindParam(":nome", $nome_limpo);
        $stmt->bindParam(":descricao", $descricao_limpa);
        $stmt->bindParam(":preco", $preco_limpo);
        $stmt->bindParam(":sku", $sku_limpo);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function lerTodos() {
        // Incluir a coluna 'stock' na seleção
        $query = "SELECT id, name, description, price, sku, stock, created_at FROM " . $this->nome_tabela . " ORDER BY created_at DESC";
        $stmt = $this->conexao->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lerUm($id) {
        // Incluir a coluna 'stock' na seleção
        $query = "SELECT id, name, description, price, sku, stock, created_at FROM " . $this->nome_tabela . " WHERE id = :id LIMIT 1";
        $stmt = $this->conexao->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $nome, $descricao, $preco, $sku) {
        // Note: A coluna 'stock' não é atualizada aqui, pois ela é manipulada apenas pelo ModeloEstoque
        $query = "UPDATE " . $this->nome_tabela . " SET name = :nome, description = :descricao, price = :preco, sku = :sku WHERE id = :id";
        $stmt = $this->conexao->prepare($query);

        $nome_limpo = htmlspecialchars(strip_tags($nome));
        $descricao_limpa = htmlspecialchars(strip_tags($descricao));
        $preco_limpo = htmlspecialchars(strip_tags($preco));
        $sku_limpo = htmlspecialchars(strip_tags($sku));

        $stmt->bindParam(":nome", $nome_limpo);
        $stmt->bindParam(":descricao", $descricao_limpa);
        $stmt->bindParam(":preco", $preco_limpo);
        $stmt->bindParam(":sku", $sku_limpo);
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