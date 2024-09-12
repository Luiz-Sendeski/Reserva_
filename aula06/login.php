<?php
session_start();
$email = NULL;
$senha = NULL;
$erro = NULL;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    require_once('includes/conexao.inc.php');
    $query = $bancoDados->prepare('SELECT id, email, senha, nome, tipo FROM pessoa WHERE email = :email');
    $query->bindParam(':email', $email);

    if ($query->execute()) {
        if ($query->rowCount() > 0) {
            $row = $query->fetch(PDO::FETCH_OBJ);
            if (password_verify($senha, $row->senha)) {
                // Incluindo o tipo do usuário no array antes de serializar e codificar para sessão
                $usuario = array('id' => $row->id, 'nome' => $row->nome, 'tipo' => $row->tipo);
                $_SESSION['usuario'] = base64_encode(serialize($usuario));

                // Atualiza a data e hora do último login
                $updateLoginTime = $bancoDados->prepare("UPDATE pessoa SET ultimo_login = NOW() WHERE id = :id");
                $updateLoginTime->bindParam(':id', $row->id);
                $updateLoginTime->execute();

                $bancoDados = NULL;
                header('Location: dashboard.php');
                exit();
            } else {
                $erro = "E-mail ou senha inválidos";
            }
        } else {
            $erro = "E-mail ou senha inválidos";
        }
    } else {
        $erro = "Erro interno";
    }
    $bancoDados = NULL;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/main.css">
    <title>Login</title>
</head>
<body>
    <div class="container-center">
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <h2>Login</h2>
            <div>
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>">
                <span></span>
            </div>
            <div>
                <label for="senha">Senha</label>
                <input type="password" name="senha" id="senha">
                <div class ="esqueceu-senha"> 
                    <a href="recuperarsenha.php">Esqueceu a senha?</a>
                </div>
                <div>
                    <p>Ainda não tem conta? <a href="usuario/cadastro.php">Cadastre-se agora</a></p>
                </div>
            </div>
            <div>
                <button type="submit">Enviar</button>
            </div>
            <span><?= htmlspecialchars($erro) ?></span>
            <div>
                <?php include('includes/mensagem.inc.php')?>
            </div>
        </form>
    </div>
</body>
</html>
