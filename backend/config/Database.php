<?php
// backend/config/Database.php

class Database {
    private $host = "localhost";
    private $db_name = "login";
    private $port = "5432";
    private $username = "postgres";
    private $password = "santosfc123"; // Certifique-se que esta senha está correta
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilita o modo de erro para exceções
            // $this->conn->exec("set names utf8"); // <--- ESTA LINHA DEVE SER REMOVIDA OU COMENTADA
        } catch(PDOException $exception) {
            // Em ambiente de desenvolvimento, você pode exibir o erro. Em produção, registre apenas.
            // echo "Erro de conexão: " . $exception->getMessage();
            // Para depuração, você pode lançar a exceção:
            throw new Exception("Erro de conexão com o banco de dados: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>