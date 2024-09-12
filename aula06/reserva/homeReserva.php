<?php
require_once('../includes/sessao.inc.php');
require_once("../includes/conexao.inc.php");

// Deserializa e decodifica a sessão do usuário
if (isset($_SESSION['usuario'])) {
    $usuario = (object) unserialize(base64_decode($_SESSION['usuario']));
    $_SESSION['is_admin'] = (isset($usuario->tipo) && $usuario->tipo === 'admin');
} else {
    header('Location: http://127.0.0.1/IFPR/aula06/login.php'); // Redireciona se não houver sessão
    exit;
}

// Verifica se o usuário está logado e redireciona caso não esteja
if (!verificaSessao()) {
    header('Location: http://127.0.0.1/IFPR/aula06/login.php');
    exit;
}

$reservas = [];

try {
    // Adaptando a consulta SQL com base no tipo do usuário
    if ($_SESSION['is_admin']) {
        // Administrador: pode ver todas as reservas
        $query = $bancoDados->prepare("SELECT r.id, p.nome as pessoa, l.nome as laboratorio, r.descricao, r.data, r.hora_inicio, r.hora_fim 
                                       FROM Reserva r 
                                       JOIN pessoa p ON r.pessoa_id = p.id 
                                       JOIN Laboratorio l ON r.laboratorio_id = l.id");
    } else {
        // Usuário normal: vê apenas suas próprias reservas
        $user_id = $usuario->id; // Usando a variável deserializada
        $query = $bancoDados->prepare("SELECT r.id, p.nome as pessoa, l.nome as laboratorio, r.descricao, r.data, r.hora_inicio, r.hora_fim 
                                       FROM Reserva r 
                                       JOIN pessoa p ON r.pessoa_id = p.id 
                                       JOIN Laboratorio l ON r.laboratorio_id = l.id 
                                       WHERE r.pessoa_id = :user_id");
        $query->bindParam(':user_id', $user_id);
    }

    $query->execute();
    $reservas = $query->fetchAll(PDO::FETCH_OBJ);

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/main.css">
    <title>Reservas</title>
</head>
<body>
    <div class="container">
        <ul class="menu">
            <li><a href="../dashboard.php">Home</a></li>
            <li><a href="cadastroReserva.php">Criar Reserva</a></li>
            <li><a href="../sair.php">Sair</a></li>
        </ul>
        <h1>Lista de Reservas</h1>
        <?php include("../includes/mensagem.inc.php") ?>
        <table>
            <thead>
                <tr>
                    <td>ID</td>
                    <td>Pessoa</td>
                    <td>Laboratório</td>
                    <td>Data</td>
                    <td>Hora Início</td>
                    <td>Hora Fim</td>
                    <td>Ações</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservas)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Nenhuma reserva encontrada</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reservas as $reserva): ?>
                        <tr>
                            <td><?= htmlspecialchars($reserva->id) ?></td>
                            <td><?= htmlspecialchars($reserva->pessoa) ?></td>
                            <td><?= htmlspecialchars($reserva->laboratorio) ?></td>
                            <td><?= htmlspecialchars($reserva->data) ?></td>
                            <td><?= htmlspecialchars($reserva->hora_inicio) ?></td>
                            <td><?= htmlspecialchars($reserva->hora_fim) ?></td>
                            <td>
                            <a href="editarReserva.php?id=<?= $reserva->id ?>" class="btn-editar">Editar</a>
                                <a href="excluirReserva.php?id=<?= $reserva->id ?>" class="btn-excluir">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
