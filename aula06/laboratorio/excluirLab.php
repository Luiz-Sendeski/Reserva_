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

        $query = $bancoDados->prepare("DELETE FROM Laboratorio WHERE id = :id");
        $query->bindParam(':id', $id);

        if ($query->execute()) {
            $_SESSION['mensagem_sucesso'] = 'Laboratório excluído com sucesso!';
            header('Location: homeLab.php');
            exit;  // Assegura que o script termina após o redirecionamento
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao tentar excluir o laboratório';
        }
    } else {
        $_SESSION['mensagem_erro'] = 'Laboratório não localizado';
    }
    header('Location: homeLab.php');
    exit;  // Assegura que o script termina após o redirecionamento
}
?>
