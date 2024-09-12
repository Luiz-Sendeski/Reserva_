<?php
//require_once('../includes/sessao.inc.php');
//if (!verificaSessao()) {
//    header('Location: http://127.0.0.1/IFPR/aula06/login.php');
//    exit;
//}
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

$nome = $email = $senha = $erroEmail = $erroNome = $erroSenha = "";
$erros = false; // Controle de erros

$usuario = NULL;
$id = NULL; // Inicializa a variável $id

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = !empty($_POST['id']) ? $_POST['id'] : NULL;
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    require_once('../includes/conexao.inc.php');

    // Validação dos campos
    if (empty($nome)) {
        $erroNome = 'Nome é obrigatório!';
        $erros = true;
    }
    if (empty($email)) {
        $erroEmail = 'E-mail é obrigatório!';
        $erros = true;
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erroEmail = 'E-mail informado não é válido!';
        $erros = true;
    }
    if (empty($senha) && is_null($id)) {
        $erroSenha = 'Senha é obrigatória!';
        $erros = true;
    }

    if (!$erros) {
        // Verificar se o e-mail já está em uso
        $query = $bancoDados->prepare('SELECT id FROM pessoa WHERE email = :email AND id <> :id');
        $query->bindParam(':email', $email);
        $query->bindParam(':id', $id);
        $query->execute();

        if ($query->rowCount() > 0) {
            $erroEmail = 'E-mail já está em uso!';
        } else {
            if (is_null($id)) {
                // Inserção de novo usuário
                $query = $bancoDados->prepare("INSERT INTO pessoa (nome, email, senha, criado_em, atualizado_em) VALUES (:nome, :email, :senha, NOW(), NOW())");
                $senha = password_hash($senha, PASSWORD_DEFAULT);
                $query->bindParam(':senha', $senha);
            } else {
                // Atualização de usuário existente
                $query = $bancoDados->prepare("UPDATE pessoa SET nome = :nome, email = :email, atualizado_em = NOW()" . (!empty($senha) ? ", senha = :senha" : "") . " WHERE id = :id");
                if (!empty($senha)) {
                    $senha = password_hash($senha, PASSWORD_DEFAULT);
                    $query->bindParam(':senha', $senha);
                }
            }

            $query->bindParam(':nome', $nome);
            $query->bindParam(':email', $email);
            if (!is_null($id)) {
                $query->bindParam(':id', $id);
            }

            if ($query->execute()) {
                $_SESSION['mensagem_sucesso'] = is_null($id) ? "Usuário cadastrado com sucesso" : "Usuário atualizado com sucesso";
                header('Location: home.php');
                exit();
            } else {
                echo 'Erro ao inserir/atualizar dados no banco!';
            }
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    require_once('../includes/conexao.inc.php');
    $id = $_GET['id'];
    $query = $bancoDados->prepare('SELECT id, nome, email FROM pessoa WHERE id = :id');
    $query->bindParam(':id', $id);

    if ($query->execute()) {
        if ($query->rowCount() > 0) {
            $usuario = $query->fetch(PDO::FETCH_OBJ);

            $nome = $usuario->nome;
            $email = $usuario->email;
        } else {
            $_SESSION['mensagem_erro'] = "Usuário não localizado";
            header('Location: home.php');
            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/main.css">
    <title>Cadastro de Usuário</title>
</head>
<body>
    <div class="container">
        <ul class="menu">
            <li><a href="home.php">Home</a></li>
            <li><a href="cadastro.php">Cadastro</a></li>
            <li><a href="../sair.php">Sair</a></li>
        </ul>
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <h2>Cadastro de usuário</h2>
            <div>
                <label for="nome">Nome</label>
                <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($nome) ?>">
                <span><?= $erroNome ?></span>
            </div>
            <div>
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>">
                <span><?= $erroEmail ?></span>
            </div>
            <div>
                <label for="senha">Senha</label>
                <input type="password" name="senha" id="senha">
                <span><?= $erroSenha ?></span>
            </div>
            <button type="submit" class="btn-salvar">Salvar</button>
        </form>
    </div>
</body>
</html>
