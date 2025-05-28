<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
<<<<<<< HEAD
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=postgres", "postgres", "santosfc123");
=======
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=login", "postgres", "santosfc123");
>>>>>>> 6a1e99a490e7a70324a1eb194a411ddde497eaa0
    echo "Conexão com PostgreSQL estabelecida com sucesso!";
} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
}