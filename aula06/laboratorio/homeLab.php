<?php
require_once('../includes/sessao.inc.php');
require_once("../includes/conexao.inc.php");  // Inclui a conexão usando PDO

// Deserializa e decodifica a sessão do usuário
if (isset($_SESSION['usuario'])) {
    $usuario = unserialize(base64_decode($_SESSION['usuario']));
    $_SESSION['is_admin'] = (isset($usuario['tipo']) && $usuario['tipo'] === 'adm');
} else {
    header('Location: http://127.0.0.1/IFPR/aula06/login.php'); // Redireciona se não houver sessão
    exit;
}

// Verifica se o usuário está logado e redireciona caso não esteja
if (!verificaSessao()) {
    header('Location: http://127.0.0.1/IFPR/aula06/login.php');
    exit;
}

// Verifica se o usuário é um administrador
if ($_SESSION['is_admin']) {
    // Administrador: pode ver todos os laboratórios
    $sql = "SELECT id, nome, numero_computadores, bloco, sala, liberado FROM Laboratorio";
    $stmt = $bancoDados->prepare($sql);
} else {
    // Usuário normal: vê apenas laboratórios específicos (adapte conforme necessário)
    $sql = "SELECT id, nome, numero_computadores, bloco, sala, liberado FROM Laboratorio WHERE id = :id";
    $stmt = $bancoDados->prepare($sql);
    $stmt->bindParam(':id', $usuario['id']);  // Adaptar se necessário
}

$laboratorios = array();
if ($stmt->execute()) {
    if ($stmt->rowCount() > 0) {
        $laboratorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/main.css">
    <title>Home - Laboratórios</title>
</head>
<body>
    <div class="container">
        <ul class="menu">
            <li><a href="../dashboard.php">Home</a></li>
            <li><a href="cadastroLab.php">Cadastro de Laboratório</a></li>
            <li><a href="../sair.php">Sair</a></li>
        </ul>

        <h1>Lista de Laboratórios</h1>
        <?php include("../includes/mensagem.inc.php") ?>
        <table>
            <thead>
                <tr>
                    <td>ID</td>
                    <td>Nome</td>
                    <td>Número de Computadores</td>
                    <td>Bloco</td>
                    <td>Sala</td>
                    <td>Liberado</td>
                    <td>Ações</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laboratorios)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Nenhum laboratório encontrado</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($laboratorios as $lab): ?>
                        <tr>
                            <td><?= htmlspecialchars($lab['id']) ?></td>
                            <td><?= htmlspecialchars($lab['nome']) ?></td>
                            <td><?= htmlspecialchars($lab['numero_computadores']) ?></td>
                            <td><?= htmlspecialchars($lab['bloco']) ?></td>
                            <td><?= htmlspecialchars($lab['sala']) ?></td>
                            <td><?= $lab['liberado'] ? 'Sim' : 'Não' ?></td>
                            <td>
                                <a href="cadastroLab.php?id=<?= $lab['id'] ?>" class="btn-editar">Editar</a>
                                <?php if ($_SESSION['is_admin']): ?>
                                    <a href="excluirLab.php?id=<?= $lab['id'] ?>" class="btn-excluir">Excluir</a>
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
