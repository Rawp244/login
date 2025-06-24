<?php
// backend/model/UserModel.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Logger.php';

class UserModel {
    private $conn;
    private $logger;
    private $table_name = "public.usuarios"; // Sua tabela de usuários

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->logger = new Logger();
    }

    public function salvarUsuario($username, $senha, $role = 'user') {
        if ($this->buscarUsuarioPorUsername($username)) {
            return ["erro" => "Nome de usuário já existe."];
        }

        $query = "INSERT INTO " . $this->table_name . " (username, senha, role) VALUES (:username, :senha, :role)";
        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($senha, PASSWORD_BCRYPT);

        // Ajustado para usar o nome de usuário (que contém o e-mail) para sanitização
        $username_sanitized = htmlspecialchars(strip_tags($username));
        $role_sanitized = htmlspecialchars(strip_tags($role));

        $stmt->bindParam(":username", $username_sanitized);
        $stmt->bindParam(":senha", $hashed_password);
        $stmt->bindParam(":role", $role_sanitized);

        try {
            if ($stmt->execute()) {
                $this->logger->log(Logger::INFO, "Usuário registrado: " . $username_sanitized);
                return ["mensagem" => "Usuário registrado com sucesso."];
            }
        } catch (PDOException $e) {
            $this->logger->log(Logger::ERROR, "Erro ao registrar usuário: " . $e->getMessage());
            return ["erro" => "Erro ao registrar usuário: " . $e->getMessage()];
        }
        return ["erro" => "Não foi possível registrar o usuário."];
    }

    public function buscarUsuarioPorUsername($username) {
        $query = "SELECT id, username, senha, role FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $username_sanitized = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(":username", $username_sanitized);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function lerTodosUsuarios() {
        // CORREÇÃO AQUI: Selecionar 'username' (que contém o e-mail) e ordenar por 'id'
        $query = "SELECT id, username, role FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function atualizarPerfilUsuario($id, $role) {
        $query = "UPDATE " . $this->table_name . " SET role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $role_sanitized = htmlspecialchars(strip_tags($role));

        $stmt->bindParam(":role", $role_sanitized);
        $stmt->bindParam(":id", $id);

        try {
            if ($stmt->execute()) {
                $this->logger->log(Logger::INFO, "Perfil do usuário ID {$id} atualizado para: " . $role_sanitized);
                return ["mensagem" => "Perfil do usuário atualizado com sucesso."];
            }
        } catch (PDOException $e) {
            $this->logger->log(Logger::ERROR, "Erro ao atualizar perfil do usuário ID {$id}: " . $e->getMessage());
            return ["erro" => "Não foi possível atualizar o perfil do usuário."];
        }
        return ["erro" => "Não foi possível atualizar o perfil do usuário."];
    }

    public function deletarUsuario($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        try {
            if ($stmt->execute()) {
                $this->logger->log(Logger::INFO, "Usuário ID {$id} deletado.");
                return ["mensagem" => "Usuário deletado com sucesso."];
            }
        } catch (PDOException $e) {
            $this->logger->log(Logger::ERROR, "Erro ao deletar usuário ID {$id}: " . $e->getMessage());
        }
        return ["erro" => "Não foi possível deletar o usuário."];
    }
}
?>