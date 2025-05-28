<?php
function getConexao() {
    return new PDO("pgsql:host=localhost;port=5432;dbname=login", "postgres", "santosfc123");
}
