<?php
// backend/utils/Auth.php

// CORREÇÃO FINAL DO CAMINHO PARA O AUTOLOAD.PHP:
// Auth.php está em backend/utils/.
// Para chegar em vendor/autoload.php (que está em C:\xampp\htdocs\loginmvc\vendor),
// precisamos subir 2 níveis (utils -> backend), e então descer para vendor.
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/Logger.php'; // Logger.php está na mesma pasta utils/

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private static $secret_key = 'NK/tEnnNSGQBCpfv7eBj6Knta/LBOAM6dijxyNZJYr8=';
    private static $alg = 'HS256';

    public static function validateToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (empty($authHeader)) {
            return null;
        }

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];

            try {
                $decoded = JWT::decode($token, new Key(self::$secret_key, self::$alg));
                return (array) $decoded->data;

            } catch (Exception $e) {
                $logger = new Logger(); 
                $logger->log(Logger::ERROR, "Erro de validação JWT: " . $e->getMessage());
                return null;
            }
        }
        return null;
    }

    public static function getUserId() {
        $payload = self::validateToken();
        if ($payload && isset($payload['user_id'])) {
            return $payload['user_id'];
        }
        return null;
    }

    public static function getUserProfile() {
        $payload = self::validateToken();
        if ($payload && isset($payload['profile'])) {
            return $payload['profile'];
        }
        return null;
    }
}