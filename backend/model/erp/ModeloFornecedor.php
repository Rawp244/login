<?php
// backend/model/erp/ModeloFornecedor.php

// Correção: Caminho para Database.php
require_once __DIR__ . '/../../config/Database.php';
// Adicionar o Logger se você pretende usá-lo neste modelo, ou remover se não
// require_once __DIR__ . '/../../utils/Logger.php'; 

class ModeloFornecedor {
    private $conexao;
    private $nome_tabela = "erp.suppliers";
    // private $logger; // Descomente se for usar Logger aqui

    public function __construct() {
        $database = new Database();
        $this->conexao = $database->getConnection();
        // $this->logger = new Logger(); // Descomente se for usar Logger aqui
    }

    public function criar($nome, $pessoa_contato, $telefone, $email, $endereco) {
        $query = "INSERT INTO " . $this->nome_tabela . " (name, contact_person, phone, email, address) VALUES (:nome, :pessoa_contato, :telefone, :email, :endereco)";
        $stmt = $this->conexao->prepare($query);

        $nome_limpo = htmlspecialchars(strip_tags($nome));
        $pessoa_contato_limpa = htmlspecialchars(strip_tags($pessoa_contato));
        $telefone_limpo = htmlspecialchars(strip_tags($telefone));
        $email_limpo = htmlspecialchars(strip_tags($email));
        $endereco_limpo = htmlspecialchars(strip_tags($endereco));

        $stmt->bindParam(":nome", $nome_limpo);
        $stmt->bindParam(":pessoa_contato", $pessoa_contato_limpa);
        $stmt->bindParam(":telefone", $telefone_limpo);
        $stmt->bindParam(":email", $email_limpo);
        $stmt->bindParam(":endereco", $endereco_limpo);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function lerTodos() {
        $query = "SELECT id, name, contact_person, phone, email, address, created_at FROM " . $this->nome_tabela . " ORDER BY created_at DESC";
        $stmt = $this->conexao->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lerUm($id) {
        $query = "SELECT id, name, contact_person, phone, email, address, created_at FROM " . $this->nome_tabela . " WHERE id = :id LIMIT 1";
        $stmt = $this->conexao->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $nome, $pessoa_contato, $telefone, $email, $endereco) {
        $query = "UPDATE " . $this->nome_tabela . " SET name = :nome, contact_person = :pessoa_contato, phone = :telefone, email = :email, address = :endereco WHERE id = :id";
        $stmt = $this->conexao->prepare($query);

        $nome_limpo = htmlspecialchars(strip_tags($nome));
        $pessoa_contato_limpa = htmlspecialchars(strip_tags($pessoa_contato));
        $telefone_limpo = htmlspecialchars(strip_tags($telefone));
        $email_limpo = htmlspecialchars(strip_tags($email));
        $endereco_limpo = htmlspecialchars(strip_tags($endereco));

        $stmt->bindParam(":nome", $nome_limpo);
        $stmt->bindParam(":pessoa_contato", $pessoa_contato_limpa);
        $stmt->bindParam(":telefone", $telefone_limpo);
        $stmt->bindParam(":email", $email_limpo);
        $stmt->bindParam(":endereco", $endereco_limpo);
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