<?php 
require_once('../includes/sessao.inc.php');
if (!verificaSessao()) {
    header('Location: http://127.0.0.1/IFPR/aula06/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        require_once('../includes/conexao.inc.php');

        // Prepara a consulta para excluir a reserva
        $query = $bancoDados->prepare("DELETE FROM Reserva WHERE id = :id");
        $query->bindParam(':id', $id);

        if ($query->execute()) {
            $_SESSION['mensagem_sucesso'] = 'Reserva excluída com sucesso!';
            header('Location: homeReserva.php');
            exit;  // Assegura que o script termina após o redirecionamento
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao excluir a reserva.';
            header('Location: homeReserva.php');
            exit;  // Assegura que o script termina após o redirecionamento
        }
    } else {
        $_SESSION['mensagem_erro'] = 'Reserva não localizada';
        header('Location: homeReserva.php');
        exit;  // Assegura que o script termina após o redirecionamento
    }
}
?>
