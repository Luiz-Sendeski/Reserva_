<?php
session_start(); // Inicia a sessão

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require 'libs/PHPMailer/src/Exception.php'; 
require 'libs/PHPMailer/src/PHPMailer.php'; 
require 'libs/PHPMailer/src/SMTP.php'; 


$email = ''; // Defina uma variável vazia para o email
$erro = '';  // Defina uma variável vazia para o erro

if($_SERVER['REQUEST_METHOD']=='POST'){
    
    // Recebe o email digitado pelo usuário
    $email = $_POST['email'];
    require_once('includes/conexao.inc.php');
    $query = $bancoDados->prepare("SELECT id, nome, senha FROM pessoa WHERE email = :email");
    $query->bindParam(':email', $email);
    
    if($query->execute()){
        if($query->rowCount() > 0){
           $usuario = $query->fetch(PDO::FETCH_OBJ);
            $novaSenha = strtoupper(bin2hex(random_bytes(4)));
            $senhaTemp = password_hash($novaSenha, PASSWORD_DEFAULT);
            
            $query = $bancoDados->prepare("UPDATE pessoa SET senha = :senha WHERE email = :email");
            $query->bindParam(':senha', $senhaTemp);
            $query->bindParam(':email', $email);

            if($query->execute()){
                $url = 'http://' . $_SERVER['SERVER_NAME'] . '/ifpr/aula/login.php';
                $htmlView = file_get_contents('views/recuperarsenha.html');

                $htmlView = str_replace('{{nome}}', $usuario->nome, $htmlView);
                $htmlView = str_replace('{{senha}}', $novaSenha, $htmlView);
                $htmlView = str_replace('{{url}}', $url, $htmlView);

                $mail = new PHPMailer;
                $mail->isSMTP();
                $mail->SMTPDebug = 0;
                $mail->CharSet = 'UTF-8';
                $mail->Host ='sandbox.smtp.mailtrap.io';
                $mail->Port = 2525;
                $mail->SMTPAuth = true;
                $mail->Username = 'edda1ff0ccd443';
                $mail->Password = 'df6800cfac0ec8';
                $mail->setFrom('noreplay@email.com', 'Serviço de recuperação de senha');
                $mail->addAddress($email, $usuario->nome);
                $mail->Subject = 'Recuperação de senha';
                $mail->msgHTML($htmlView);

                $bancoDados = NULL;
                if($mail->send()){
                    $_SESSION['mensagem_sucesso'] = "Um e-mail com a sua senha foi enviado para:" . $email;
                    header('Location: login.php');
                    exit();

                }else{
                    $bancoDados = NULL;
                    $erro= "Erro interno";
                }
            }else{
                $erro = "Erro ao atualizar a senha.";
                $bancoDados = NULL;
            }

           
        }else{
            $erro = "E-mail não encontrado.";
            $bancoDados = NULL;
        }
    }else{
        $erro = "Erro interno!";
        $bancoDados = NULL;
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/main.css">
    <title>Recuperar Senha</title>
</head>
<body>
    <div class="container-center">
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <h2>Recuperar Senha</h2>
            <p>Digite seu e-mail para receber uma nova senha.</p>
            
            <div>
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required>
                <span><?= htmlspecialchars($erro) ?></span>
            </div>
            
            <div>
                <button type="submit">Enviar</button>
            </div>
        </form>
    </div>
</body>
</html>
