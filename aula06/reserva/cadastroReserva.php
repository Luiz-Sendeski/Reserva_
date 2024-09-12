<?php
session_start(); // Garante que a sessão é iniciada no começo do script

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('../includes/sessao.inc.php');
require_once("../includes/conexao.inc.php");

// Verifica se o usuário está logado
if (!verificaSessao()) {
    header('Location: http://127.0.0.1/IFPR/aula06/login.php');
    exit;
}

// Deserializa e decodifica a sessão do usuário
if (isset($_SESSION['usuario'])) {
    $usuario = unserialize(base64_decode($_SESSION['usuario']));
    $_SESSION['is_admin'] = ($usuario['tipo'] === 'a');
} else {
    header('Location: http://127.0.0.1/IFPR/aula06/login.php');
    exit;
}

require_once('../libs/PHPMailer/src/Exception.php');
require_once('../libs/PHPMailer/src/PHPMailer.php');
require_once('../libs/PHPMailer/src/SMTP.php');

$pessoa_id = $laboratorio_id = $descricao = $data = $hora_inicio = $hora_fim = "";
$erroPessoa = $erroLaboratorio = $erroDescricao = $erroData = $erroHoraInicio = $erroHoraFim = "";
$erros = false;
$reserva = NULL;
$id = NULL;

// Consulta para obter a lista de pessoas e laboratórios
$pessoas = $bancoDados->query("SELECT id, nome FROM pessoa")->fetchAll(PDO::FETCH_OBJ);
$laboratorios = $bancoDados->query("SELECT id, nome, liberado FROM Laboratorio")->fetchAll(PDO::FETCH_OBJ);

if (!$pessoas || !$laboratorios) {
    die("Erro ao buscar dados do banco: " . $bancoDados->errorInfo()[2]);
}

// Processo de manipulação do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = !empty($_POST['id']) ? $_POST['id'] : NULL;
    $pessoa_id = $_POST['pessoa_id'] ?? $usuario['id'];
    $laboratorio_id = $_POST['laboratorio_id'];
    $descricao = $_POST['descricao'];
    $data = $_POST['data'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fim = $_POST['hora_fim'];

    // Validações
    $erroPessoa = empty($pessoa_id) ? 'Pessoa é obrigatória!' : '';
    $erroLaboratorio = empty($laboratorio_id) ? 'Laboratório é obrigatório!' : '';
    $erroDescricao = empty($descricao) ? 'Descrição é obrigatória!' : '';
    
    // Verificação de data futura
    $data_atual = date('Y-m-d');
    if (empty($data) || $data < $data_atual) {
        $erroData = 'A data deve ser futura!';
    }

    // Verificação de horários
    if (empty($hora_inicio)) {
        $erroHoraInicio = 'Hora de Início é obrigatória!';
    }
    if (empty($hora_fim)) {
        $erroHoraFim = 'Hora de Fim é obrigatória!';
    }
    // Verifica se a hora de fim é superior à hora de início
    if ($hora_fim <= $hora_inicio) {
        $erroHoraFim = 'A hora de término deve ser maior que a hora de início!';
    }
    // Verifica se a reserva está limitada ao mesmo dia (não pode passar de 23:59)
    if ($hora_fim == '00:00' || $hora_fim < $hora_inicio) {
        $erroHoraFim = 'A reserva não pode ultrapassar o final do dia (23:59). Divida a reserva em dois dias.';
    }

    $erros = $erroPessoa || $erroLaboratorio || $erroDescricao || $erroData || $erroHoraInicio || $erroHoraFim;

    if (!$erros) {
        // Verifica se já existe uma reserva no mesmo horário
        $query = $bancoDados->prepare("SELECT COUNT(*) FROM Reserva WHERE laboratorio_id = :laboratorio_id AND data = :data AND ((:hora_inicio < hora_fim AND :hora_fim > hora_inicio))");
        $query->bindParam(':laboratorio_id', $laboratorio_id);
        $query->bindParam(':data', $data);
        $query->bindParam(':hora_inicio', $hora_inicio);
        $query->bindParam(':hora_fim', $hora_fim);
        $query->execute();
        $conflito = $query->fetchColumn();

        if ($conflito > 0) {
            $_SESSION['mensagem_erro'] = 'Já existe uma reserva para este horário no laboratório selecionado.';
        } else {
            // Insere ou atualiza a reserva
            $sql = is_null($id) ? "INSERT INTO Reserva (pessoa_id, laboratorio_id, descricao, data, hora_inicio, hora_fim, criado_em, atualizado_em) VALUES (:pessoa_id, :laboratorio_id, :descricao, :data, :hora_inicio, :hora_fim, NOW(), NOW())" : "UPDATE Reserva SET pessoa_id = :pessoa_id, laboratorio_id = :laboratorio_id, descricao = :descricao, data = :data, hora_inicio = :hora_inicio, hora_fim = :hora_fim, atualizado_em = NOW() WHERE id = :id";
            $query = $bancoDados->prepare($sql);
            if (!is_null($id)) {
                $query->bindParam(':id', $id);
            }
            $query->bindParam(':pessoa_id', $pessoa_id);
            $query->bindParam(':laboratorio_id', $laboratorio_id);
            $query->bindParam(':descricao', $descricao);
            $query->bindParam(':data', $data);
            $query->bindParam(':hora_inicio', $hora_inicio);
            $query->bindParam(':hora_fim', $hora_fim);

            if ($query->execute()) {
                $_SESSION['mensagem_sucesso'] = is_null($id) ? "Reserva criada com sucesso!" : "Reserva atualizada com sucesso!";
                
                // Chama a função para enviar o email
                enviarEmailReserva($pessoa_id, $descricao, $data, $hora_inicio, $hora_fim, $bancoDados, $laboratorio_id);

                header('Location: homeReserva.php');
                exit();
            } else {
                $_SESSION['mensagem_erro'] = 'Erro ao inserir/atualizar a reserva.';
            }
        }
    }
}

// Função para enviar email reativada e otimizada
function enviarEmailReserva($pessoaId, $descricao, $data, $horaInicio, $horaFim, $bancoDados, $laboratorioId) {
    // Obter os dados da pessoa
    $query = $bancoDados->prepare("SELECT nome, email FROM pessoa WHERE id = :pessoa_id");
    $query->bindParam(':pessoa_id', $pessoaId);
    $query->execute();
    $pessoa = $query->fetch(PDO::FETCH_OBJ);

    // Obter os dados do laboratório
    $query = $bancoDados->prepare("SELECT nome FROM Laboratorio WHERE id = :laboratorio_id");
    $query->bindParam(':laboratorio_id', $laboratorioId);
    $query->execute();
    $laboratorio = $query->fetch(PDO::FETCH_OBJ);

    if (!$pessoa || !$laboratorio) {
        return; // Retorna se não encontrar os dados
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = 'f5a81c40ba3c69'; // Verifique suas credenciais
        $mail->Password = 'fc30475aae7682'; // Verifique suas credenciais
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 2525;

        // Definir charset para UTF-8
        $mail->CharSet = 'UTF-8';

        // Carregar o conteúdo HTML e substituir os placeholders
        $htmlView = file_get_contents(__DIR__ . '/../views/confirmacaoReservaLaboratorio.html');
        $htmlView = str_replace(
            ['{{nome}}', '{{laboratorio}}', '{{data_reserva}}', '{{hora_inicio}}', '{{hora_fim}}', '{{descricao}}', '{{url}}'], 
            [$pessoa->nome, $laboratorio->nome, $data, $horaInicio, $horaFim, $descricao, 'https://seuwebsite.com'], 
            $htmlView
        );

        // Definir informações de remetente e destinatário
        $mail->setFrom('noreply@seuwebsite.com', 'Reservas de Laboratório');
        $mail->addAddress($pessoa->email, $pessoa->nome);

        // Definir o assunto e corpo do e-mail
        $mail->Subject = 'Confirmação de Reserva de Laboratório';
        $mail->msgHTML($htmlView);

        // Enviar o e-mail
        $mail->send();
    } catch (Exception $e) {
        echo 'Erro ao enviar e-mail: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/main.css">
    <title><?= is_null($id) ? 'Criar Reserva' : 'Editar Reserva' ?></title>
</head>
<body>
    <div class="container">
        <ul class="menu">
            <li><a href="../dashboard.php">Home</a></li>
            <li><a href="homeReserva.php">Reservas</a></li>
            <li><a href="../sair.php">Sair</a></li>
        </ul>
        <h1><?= is_null($id) ? 'Criar Reserva' : 'Editar Reserva' ?></h1>
        <?php include("../includes/mensagem.inc.php"); ?>
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <div>
                <label for="pessoa_id">Pessoa</label>
                <select name="pessoa_id" id="pessoa_id" required>
                    <?php if ($_SESSION['is_admin']) : ?>
                        <?php foreach ($pessoas as $pessoa): ?>
                            <option value="<?= $pessoa->id ?>" <?= $pessoa_id == $pessoa->id ? 'selected' : '' ?>><?= htmlspecialchars($pessoa->nome) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="<?= $usuario['id'] ?>" selected><?= htmlspecialchars($usuario['nome']) ?></option>
                    <?php endif; ?>
                </select>
                <span><?= $erroPessoa ?></span>
            </div>
            <div>
                <label for="laboratorio_id">Laboratório</label>
                <select name="laboratorio_id" id="laboratorio_id" required>
                    <option value="">Selecione um laboratório</option>
                    <?php foreach ($laboratorios as $laboratorio): ?>
                        <option value="<?= $laboratorio->id ?>" <?= $laboratorio_id == $laboratorio->id ? 'selected' : '' ?>><?= htmlspecialchars($laboratorio->nome) ?> <?= (!$laboratorio->liberado ? '(Não liberado)' : '') ?></option>
                    <?php endforeach; ?>
                </select>
                <span><?= $erroLaboratorio ?></span>
            </div>
            <div>
                <label for="descricao">Descrição</label>
                <textarea name="descricao" id="descricao" rows="3" required><?= htmlspecialchars($descricao) ?></textarea>
                <span><?= $erroDescricao ?></span>
            </div>
            <div>
                <label for="data">Data</label>
                <input type="date" name="data" id="data" value="<?= htmlspecialchars($data) ?>" required>
                <span><?= $erroData ?></span>
            </div>
            <div>
                <label for="hora_inicio">Hora de Início</label>
                <input type="time" name="hora_inicio" id="hora_inicio" value="<?= htmlspecialchars($hora_inicio) ?>" required>
                <span><?= $erroHoraInicio ?></span>
            </div>
            <div>
                <label for="hora_fim">Hora de Fim</label>
                <input type="time" name="hora_fim" id="hora_fim" value="<?= htmlspecialchars($hora_fim) ?>" required>
                <span><?= $erroHoraFim ?></span>
            </div>
            <button type="submit" class="btn-salvar"><?= is_null($id) ? 'Criar Reserva' : 'Salvar Alterações' ?></button>
        </form>
    </div>
</body>
</html>
