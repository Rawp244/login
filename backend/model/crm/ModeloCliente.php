<?php
// backend/model/crm/ModeloCliente.php
require_once __DIR__ . '/../../config/Database.php';

class ModeloCliente {
    private $conexao;
    private $nome_tabela = "crm.clients";

    public function __construct() {
        $database = new Database();
        $this->conexao = $database->getConnection();
    }

    public function criar($nome, $email, $telefone, $endereco, $cpf, $data_nascimento) {
        $query = "INSERT INTO " . $this->nome_tabela . " (name, email, phone, address, cpf, birth_date) VALUES (:nome, :email, :telefone, :endereco, :cpf, :data_nascimento)";
        $stmt = $this->conexao->prepare($query);

        $nome_limpo = htmlspecialchars(strip_tags($nome));
        $email_limpo = htmlspecialchars(strip_tags($email));
        $telefone_limpo = htmlspecialchars(strip_tags($telefone));
        $endereco_limpo = htmlspecialchars(strip_tags($endereco));
        $cpf_limpo = htmlspecialchars(strip_tags($cpf));

        $stmt->bindParam(":nome", $nome_limpo);
        $stmt->bindParam(":email", $email_limpo);
        $stmt->bindParam(":telefone", $telefone_limpo);
        $stmt->bindParam(":endereco", $endereco_limpo);
        $stmt->bindParam(":cpf", $cpf_limpo);
        // $data_nascimento pode ser null
        if ($data_nascimento === null || $data_nascimento === '') {
            $stmt->bindValue(":data_nascimento", null, PDO::PARAM_NULL);
        } else {
            $data_nascimento_limpa = htmlspecialchars(strip_tags($data_nascimento));
            $stmt->bindParam(":data_nascimento", $data_nascimento_limpa);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function lerTodos() {
        $query = "SELECT id, name, email, phone, address, cpf, birth_date, created_at FROM " . $this->nome_tabela . " ORDER BY created_at DESC";
        $stmt = $this->conexao->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lerUm($id) {
        $query = "SELECT id, name, email, phone, address, cpf, birth_date, created_at FROM " . $this->nome_tabela . " WHERE id = :id LIMIT 1";
        $stmt = $this->conexao->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $nome, $email, $telefone, $endereco, $cpf, $data_nascimento) {
        $query = "UPDATE " . $this->nome_tabela . " SET name = :nome, email = :email, phone = :telefone, address = :endereco, cpf = :cpf, birth_date = :data_nascimento WHERE id = :id";
        $stmt = $this->conexao->prepare($query);

        $nome_limpo = htmlspecialchars(strip_tags($nome));
        $email_limpo = htmlspecialchars(strip_tags($email));
        $telefone_limpo = htmlspecialchars(strip_tags($telefone));
        $endereco_limpo = htmlspecialchars(strip_tags($endereco));
        $cpf_limpo = htmlspecialchars(strip_tags($cpf));

        $stmt->bindParam(":nome", $nome_limpo);
        $stmt->bindParam(":email", $email_limpo);
        $stmt->bindParam(":telefone", $telefone_limpo);
        $stmt->bindParam(":endereco", $endereco_limpo);
        $stmt->bindParam(":cpf", $cpf_limpo);
        if ($data_nascimento === null || $data_nascimento === '') {
            $stmt->bindValue(":data_nascimento", null, PDO::PARAM_NULL);
        } else {
            $data_nascimento_limpa = htmlspecialchars(strip_tags($data_nascimento));
            $stmt->bindParam(":data_nascimento", $data_nascimento_limpa);
        }
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