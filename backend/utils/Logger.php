<?php
// backend/utils/Logger.php

require_once __DIR__ . '/../config/Database.php';

class Logger {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

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
            // Em caso de erro ao gravar o log, tente escrever em um arquivo de fallback
            $fallbackLogFile = __DIR__ . '/../logs/application_errors.log';
            $logEntry = "[" . date('Y-m-d H:i:s') . "] [ERROR_LOGGING_TO_DB] Level: " . $level . " | Message: " . $message . " | Context: " . json_encode($context) . " | DB_Error: " . $e->getMessage() . PHP_EOL;
            error_log($logEntry, 3, $fallbackLogFile);
            return false;
        }
    }

    public function error($message, $context = []) {
        return $this->log('ERROR', $message, $context);
    }

    public function info($message, $context = []) {
        return $this->log('INFO', $message, $context);
    }

    public function warning($message, $context = []) {
        return $this->log('WARNING', $message, $context);
    }
}
?>