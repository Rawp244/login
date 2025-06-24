<?php
// backend/utils/Logger.php

// O caminho para Database.php está correto aqui, pois Logger.php está em backend/utils/
// e Database.php está em backend/config/.
require_once __DIR__ . '/../config/Database.php';

class Logger {
    // Definindo as constantes para os níveis de log.
    // Isso permite que outros arquivos usem Logger::INFO, Logger::WARNING, Logger::ERROR
    // como constantes, que é o que o ModeloEstoque.php está tentando fazer.
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        // Opcional: Criar a tabela de logs se não existir (para desenvolvimento)
        // $this->createLogsTableIfNotExists();
    }

    // Opcional: Método para criar a tabela de logs (útil em desenvolvimento)
    // private function createLogsTableIfNotExists() {
    //     $query = "
    //         CREATE TABLE IF NOT EXISTS logs (
    //             id SERIAL PRIMARY KEY,
    //             level VARCHAR(20) NOT NULL,
    //             message TEXT NOT NULL,
    //             context JSONB,
    //             created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    //         );
    //     ";
    //     try {
    //         $this->conn->exec($query);
    //     } catch (PDOException $e) {
    //         error_log("Erro ao criar tabela de logs: " . $e->getMessage());
    //     }
    // }

    public function log($level, $message, $context = []) {
        try {
            $query = "INSERT INTO logs (level, message, context) VALUES (:level, :message, :context)";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":level", $level);
            $stmt->bindParam(":message", $message);
            $stmt->bindValue(":context", json_encode($context), PDO::PARAM_STR); // Salva o contexto como JSON

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            // Em caso de erro ao gravar o log no DB, tente escrever em um arquivo de fallback
            $fallbackLogFile = __DIR__ . '/../logs/application_errors.log';
            $logEntry = "[" . date('Y-m-d H:i:s') . "] [ERROR_LOGGING_TO_DB] Level: " . $level . " | Message: " . $message . " | Context: " . json_encode($context) . " | DB_Error: " . $e->getMessage() . PHP_EOL;
            error_log($logEntry, 3, $fallbackLogFile);
            return false;
        }
    }

    // Métodos de atalho para logar em níveis específicos
    public function error($message, $context = []) {
        return $this->log(self::ERROR, $message, $context); // Usa a constante da própria classe
    }

    public function info($message, $context = []) {
        return $this->log(self::INFO, $message, $context); // Usa a constante da própria classe
    }

    public function warning($message, $context = []) {
        return $this->log(self::WARNING, $message, $context); // Usa a constante da própria classe
    }
}
?>