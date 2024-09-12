<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('../libs/PHPMailer/src/Exception.php');
require_once('../libs/PHPMailer/src/PHPMailer.php');
require_once('../libs/PHPMailer/src/SMTP.php');

$mail = new PHPMailer(true); // Cria uma nova instância do PHPMailer

try {
    // Configurações do servidor
    $mail->isSMTP();                                      // Define o uso de SMTP
    $mail->Host       = 'sandbox.smtp.mailtrap.io';       // Servidor SMTP
    $mail->SMTPAuth   = true;                             // Habilita autenticação SMTP
    $mail->Username   = 'edda1ff0ccd443';                 // Usuário SMTP
    $mail->Password   = 'df6800cfac0ec8';                          // Senha SMTP
    $mail->SMTPSecure = 'tls';                            // Habilita TLS, pode ser 'ssl' também
    $mail->Port       = 2525;                             // Porta TCP para a conexão

    // Destinatários
    $mail->setFrom('seu-email@example.com', 'Mailer');
    $mail->addAddress('destinatario@example.com', 'Joe User');     // Adiciona um destinatário

    // Conteúdo do e-mail
    $mail->isHTML(true);                                  // Define o formato do e-mail para HTML
    $mail->Subject = 'Aqui vai o assunto';
    $mail->Body    = 'Este é o corpo do e-mail em <b>HTML</b>';
    $mail->AltBody = 'Este é o corpo do e-mail para clientes de e-mail que não suportam HTML';

    $mail->send();
    echo 'Mensagem enviada com sucesso';
} catch (Exception $e) {
    echo "Mensagem não pôde ser enviada. Erro: {$mail->ErrorInfo}";
}
?>
