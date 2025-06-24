<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=login", "postgres", "santosfc123");
    echo "Conexão com PostgreSQL estabelecida com sucesso!";
} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
}
?>