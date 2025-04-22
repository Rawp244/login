<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

class UserModel {
    public function salvarUsuario($email, $senha) {
        $pdo = getConexao();
        $sql = "INSERT INTO usuarios (email, senha) VALUES (:email, :senha)";
        $stmt = $pdo->prepare($sql);
        $senhaCriptografada = password_hash($senha, PASSWORD_BCRYPT);

        try {
            $stmt->execute([
                ':email' => $email,
                ':senha' => $senhaCriptografada
            ]);
            return ["mensagem" => "Usuário salvo com sucesso"];
        } catch (PDOException $e) {
            return ["erro" => "Erro ao salvar usuário: " . $e->getMessage()];
        }
    }
}
