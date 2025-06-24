<?php
// backend/utils/EmailService.php

// Incluir o autoloader do Composer (necessário para o PHPMailer)
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/Logger.php'; // Logger.php está na mesma pasta utils/
require_once __DIR__ . '/../model/erp/ModeloCliente.php'; // Para buscar os clientes

class EmailService {
    private $logger;
    private $modeloCliente;

    public function __construct() {
        $this->logger = new Logger();
        $this->modeloCliente = new ModeloCliente(); // Instância do ModeloCliente
    }

    public function enviarPromocaoParaTodosClientes($promocao) {
        // CORREÇÃO AQUI: Chamar a função correta do ModeloCliente
        $clientes = $this->modeloCliente->lerTodos(); // Alterado de lerTodosClientes() para lerTodos()

        if (empty($clientes)) {
            $this->logger->log(Logger::INFO, "Nenhum cliente encontrado para enviar promoção.");
            return true;
        }

        foreach ($clientes as $cliente) {
            $clienteEmail = $cliente['email'];
            $clienteName = $cliente['name'];

            if (!filter_var($clienteEmail, FILTER_VALIDATE_EMAIL)) {
                $this->logger->log(Logger::WARNING, "E-mail inválido para o cliente " . $clienteName . " (ID: " . $cliente['id'] . "): " . $clienteEmail);
                continue;
            }

            $assunto = "Nova Promoção Imperdível: " . $promocao['titulo'];
            $corpoHtml = $this->montarCorpoEmailPromocao($promocao, $clienteName);
            $corpoTexto = "Olá " . $clienteName . "!\n\nConfira nossa nova promoção:\n\n" .
                          "Título: " . $promocao['titulo'] . "\n" .
                          "Descrição: " . ($promocao['descricao'] ?? 'N/A') . "\n" .
                          "Data de Início: " . ($promocao['data_inicio'] ?? 'N/A') . "\n" .
                          "Data de Fim: " . ($promocao['data_fim'] ? (new DateTime($promocao['data_fim']))->format('d/m/Y') : 'N/A') . "\n" .
                          "Valor Associado: " . ($promocao['valor_associado'] ? 'R$ ' . number_format($promocao['valor_associado'], 2, ',', '.') : 'N/A') . "\n\n" .
                          "Aproveite!";

            try {
                $mail = new PHPMailer(true);
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;

                $mail->isSMTP();
                $mail->Host       = 'sandbox.smtp.mailtrap.io';
                $mail->SMTPAuth   = true;
                $mail->Username   = '8ccf0a9f6caafd';
                $mail->Password   = 'b65669e6cc9cca';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 2525;

                $mail->CharSet    = 'UTF-8';
                $mail->setLanguage('pt_br');

                $mail->setFrom('promo@seusistema.com', 'Promoções - Seu Sistema ERP/CRM');
                $mail->addAddress($clienteEmail, $clienteName);

                $mail->isHTML(true);
                $mail->Subject = $assunto;
                $mail->Body    = $corpoHtml;
                $mail->AltBody = $corpoTexto;

                $mail->send();
                $this->logger->log(Logger::INFO, "Promoção '" . $promocao['titulo'] . "' enviada para: " . $clienteEmail . " (Via Mailtrap)");
            } catch (Exception $e) {
                $this->logger->log(Logger::ERROR, "Erro ao enviar promoção '" . $promocao['titulo'] . "' para " . $clienteEmail . ": " . $mail->ErrorInfo);
            }
        }
        return true;
    }

    private function montarCorpoEmailPromocao($promocao, $clienteName) {
        $dataInicio = $promocao['data_inicio'] ? (new DateTime($promocao['data_inicio']))->format('d/m/Y') : 'N/A';
        $dataFim = $promocao['data_fim'] ? (new DateTime($promocao['data_fim']))->format('d/m/Y') : 'N/A';
        $valor = $promocao['valor_associado'] ? 'R$ ' . number_format($promocao['valor_associado'], 2, ',', '.') : 'N/A';

        $html = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;'>
                <h2 style='color: #0056b3; text-align: center; margin-bottom: 20px;'>🎉 Nova Promoção Imperdível! 🎉</h2>
                <p>Olá, <strong>" . htmlspecialchars($clienteName) . "</strong>!</p>
                <p>Temos uma novidade exclusiva para você:</p>
                
                <div style='background-color: #ffffff; padding: 15px; border-left: 5px solid #ffcc00; margin: 20px 0;'>
                    <h3 style='color: #d9534f; margin-top: 0;'>" . htmlspecialchars($promocao['titulo']) . "</h3>
                    <p>" . nl2br(htmlspecialchars($promocao['descricao'] ?? '')) . "</p>
                    <ul style='list-style: none; padding: 0;'>
                        <li style='margin-bottom: 5px;'><strong>🗓️ Início:</strong> " . $dataInicio . "</li>
                        <li style='margin-bottom: 5px;'><strong>📅 Fim:</strong> " . $dataFim . "</li>
                        <li style='margin-bottom: 5px;'><strong>💰 Valor Associado:</strong> " . $valor . "</li>
                        <li style='margin-bottom: 5px;'><strong>🔖 Status:</strong> " . htmlspecialchars($promocao['status']) . "</li>
                    </ul>
                </div>

                <p style='text-align: center; margin-top: 30px;'>Não perca tempo! Acesse nosso sistema para mais detalhes ou entre em contato.</p>
                <p style='text-align: center; margin-top: 20px;'>
                    <a href='http://localhost:3000/login' style='background-color: #28a745; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Acessar o Sistema</a>
                </p>
                <hr style='border: 0; border-top: 1px solid #eee; margin: 25px 0;'>
                <p style='font-size: 0.9em; color: #777; text-align: center;'>Este é um e-mail automático. Por favor, não responda.</p>
            </div>
        </div>
        ";
        return $html;
    }
}
