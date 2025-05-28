<?php
<<<<<<< HEAD
// backend/model/UserModel.php

require_once __DIR__ . '/../config/Database.php';

class UserModel {
    private $conn;
    private $table_name = "usuarios";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function salvarUsuario($email, $senha) {
        // Adicione um TRY-CATCH para capturar a exceção de violação de unicidade
        try {
            $query = "INSERT INTO " . $this->table_name . " (email, senha) VALUES (:email, :senha)";
            $stmt = $this->conn->prepare($query);

            $senhaHash = password_hash($senha, PASSWORD_BCRYPT); // Criptografar a senha

            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":senha", $senhaHash);

            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            // Captura a exceção de violação de unicidade (SQLSTATE[23505])
            // Isso permite que o Controller trate a mensagem de "email já existe"
            if ($e->getCode() == '23505') { // Código para Unique violation no PostgreSQL
                // Não lança o erro, apenas retorna false para indicar falha
                return false;
            }
            // Para outros erros de DB não relacionados a duplicidade, você pode relançar
            // ou logar e retornar false, dependendo da sua estratégia de erro.
            // Por enquanto, apenas retorna false para qualquer PDOException não tratada especificamente.
            return false;
        }
        return false; // Em caso de falha no execute() por algum outro motivo não capturado acima
    }

    // MÉTODO DE LOGIN E VERIFICAÇÃO DE EXISTÊNCIA (CORRIGIDO)
    // O parâmetro $senha é opcional e serve para diferenciar entre login e apenas verificação de existência
    public function buscarUsuarioPorEmailESenha($email, $senha = null) {
        $query = "SELECT id, email, senha FROM " . $this->table_name . " WHERE email = :email LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return false; // Usuário não encontrado
        }

        // Se a senha foi fornecida (para LOGIN), verifique-a
        // Verificamos se $senha não é null E não é uma string vazia
        if ($senha !== null && $senha !== '') {
            if (password_verify($senha, $usuario['senha'])) {
                return $usuario; // Login bem-sucedido: e-mail e senha corretos
            } else {
                return false; // Senha incorreta
            }
        } else {
            // Se a senha NÃO foi fornecida (ou é vazia), significa que estamos apenas
            // verificando a existência do usuário pelo e-mail.
            // Neste caso, se o usuário foi encontrado, retorne-o.
            return $usuario; // Usuário encontrado (para checagem de existência)
        }
    }
}
?>
=======
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
>>>>>>> 6a1e99a490e7a70324a1eb194a411ddde497eaa0
