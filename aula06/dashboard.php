<?php 
require_once('includes/sessao.inc.php');
if (!verificaSessao()) {
    header('Location: http://127.0.0.1/IFPR/aula06/login.php');
    exit;
}

$usuario = (object)unserialize(base64_decode($_SESSION['usuario']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/main.css">
    <title>Dashboard</title>
</head>
<body>
    <div class="container">
        <ul class="menu">
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="usuario/home.php">Usuário</a></li>
            <li><a href="laboratorio/homeLab.php">Laboratorio</a></li>
            <li><a href="reserva/homeReserva.php">Reserva De Laboratorio</a></li>
            <li><a href="sair.php">Sair</a></li>
        </ul>
        <h1>Bem-vindo, <?= htmlspecialchars($usuario->nome) ?>!</h1>
    </div>
</body>
</html>
