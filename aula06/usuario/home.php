<?php
require_once('../includes/sessao.inc.php');
require_once("../includes/conexao.inc.php");

// Deserializa e decodifica a sessão do usuário
if (isset($_SESSION['usuario'])) {
    $usuario = unserialize(base64_decode($_SESSION['usuario']));
    $_SESSION['is_admin'] = (isset($usuario['tipo']) && $usuario['tipo'] === 'admin');
} else {
    header('Location: http://127.0.0.1/IFPR/aula06/login.php'); // Redireciona se não houver sessão
    exit;
}

// Verifica se o usuário está logado e redireciona caso não esteja
if (!verificaSessao()) {
    header('Location: http://127.0.0.1/IFPR/aula06/login.php');
    exit;
}

// Adaptando a consulta SQL com base no tipo do usuário
if ($_SESSION['is_admin']) {
    // Administrador: pode ver todos os usuários
    $query = $bancoDados->prepare("SELECT id, nome, email FROM pessoa ORDER BY nome");
} else {
    // Usuário normal: vê apenas seu próprio perfil
    $user_id = $usuario['id']; // Usando a variável deserializada
    $query = $bancoDados->prepare("SELECT id, nome, email FROM pessoa WHERE id = :id");
    $query->bindParam(':id', $user_id);
}

$usuarios = array();
if ($query->execute()) {
    if ($query->rowCount() > 0) {
        $usuarios = $query->fetchAll(PDO::FETCH_OBJ);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/main.css">
    <title>Lista de usuários</title>
</head>

<body>
    <div class="container">
        <ul class="menu">
            <li><a href="../dashboard.php">Home</a></li>
            <li><a href="cadastro.php">Cadastro</a></li>
            <li><a href="../sair.php">Sair</a></li>
        </ul>
        <?php include("../includes/mensagem.inc.php") ?>
        <table>
            <thead>
                <tr>
                    <td>#</td>
                    <td>Nome</td>
                    <td>E-mail</td>
                    <td>Ações</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">Nenhum usuário encontrado</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= $usuario->id ?></td>
                            <td><?= htmlspecialchars($usuario->nome) ?></td>
                            <td><?= htmlspecialchars($usuario->email) ?></td>
                            <td>
                                <a href="cadastro.php?id=<?= $usuario->id ?>" class="btn-editar">Editar</a>
                                <?php if ($_SESSION['is_admin']): ?>
                                    <a href="excluir.php?id=<?= $usuario->id ?>" class="btn-excluir">Excluir</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>