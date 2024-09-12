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
if (!$_SESSION['is_admin']) {
    // Redireciona para o dashboard caso não seja um administrador
    header("Location: ../dashboard.php");
    exit();
}

$nome = $numero_computadores = $bloco = $sala = $liberado = "";
$erroNome = $erroNumeroComputadores = $erroBloco = $erroSala = "";
$erros = false;
$id = null; // Inicializa a variável $id para evitar o erro

$laboratorio = NULL;

// Processa o formulário de cadastro/edição de laboratório
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = !empty($_POST['id']) ? $_POST['id'] : NULL;
    $nome = trim($_POST['nome']);
    $numero_computadores = trim($_POST['numero_computadores']);
    $bloco = trim($_POST['bloco']);
    $sala = trim($_POST['sala']);
    $liberado = isset($_POST['liberado']) ? 1 : 0;

    if (empty($nome)) {
        $erroNome = 'Nome é obrigatório!';
        $erros = true;
    }
    if (empty($numero_computadores)) {
        $erroNumeroComputadores = 'Número de computadores é obrigatório!';
        $erros = true;
    }
    if (empty($bloco)) {
        $erroBloco = 'Bloco é obrigatório!';
        $erros = true;
    }
    if (empty($sala)) {
        $erroSala = 'Sala é obrigatória!';
        $erros = true;
    }

    if (!$erros) {
        if (is_null($id)) {
            $sql = "INSERT INTO Laboratorio (nome, numero_computadores, bloco, sala, liberado, criado_em, atualizado_em)
                    VALUES (:nome, :numero_computadores, :bloco, :sala, :liberado, NOW(), NOW())";
        } else {
            $sql = "UPDATE Laboratorio SET nome = :nome, numero_computadores = :numero_computadores, bloco = :bloco, sala = :sala, liberado = :liberado, atualizado_em = NOW() WHERE id = :id";
        }
        
        $stmt = $bancoDados->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':numero_computadores', $numero_computadores);
        $stmt->bindParam(':bloco', $bloco);
        $stmt->bindParam(':sala', $sala);
        $stmt->bindParam(':liberado', $liberado);

        if (!is_null($id)) {
            $stmt->bindParam(':id', $id);
        }

        if ($stmt->execute()) {
            $mensagem_sucesso = is_null($id) ? "Laboratório cadastrado com sucesso" : "Laboratório atualizado com sucesso";
            $_SESSION['mensagem_sucesso'] = $mensagem_sucesso;
            header('Location: homeLab.php');
            exit();
        } else {
            echo 'Erro ao inserir/atualizar dados no banco!';
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = $bancoDados->prepare('SELECT id, nome, numero_computadores, bloco, sala, liberado FROM Laboratorio WHERE id = :id');
    $query->bindParam(':id', $id);

    if ($query->execute()) {
        if ($query->rowCount() > 0) {
            $laboratorio = $query->fetch(PDO::FETCH_OBJ);

            $nome = $laboratorio->nome;
            $numero_computadores = $laboratorio->numero_computadores;
            $bloco = $laboratorio->bloco;
            $sala = $laboratorio->sala;
            $liberado = $laboratorio->liberado;
        } else {
            $_SESSION['mensagem_erro'] = "Laboratório não localizado";
            header('Location: homeLab.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/main.css">
    <title>Cadastro de Laboratório</title>
</head>
<body>
    <div class="container">
        <ul class="menu">
            <li><a href="../dashboard.php">Home</a></li>
            <li><a href="homeLab.php">Laboratórios</a></li>
            <li><a href="../sair.php">Sair</a></li>
        </ul>
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <h2><?= is_null($id) ? 'Cadastro de Laboratório' : 'Editar Laboratório' ?></h2>
            <div>
                <label for="nome">Nome</label>
                <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($nome) ?>" required>
                <span><?= htmlspecialchars($erroNome) ?></span>
            </div>
            <div>
                <label for="numero_computadores">Número de Computadores</label>
                <input type="number" name="numero_computadores" id="numero_computadores" value="<?= htmlspecialchars($numero_computadores) ?>" required>
                <span><?= htmlspecialchars($erroNumeroComputadores) ?></span>
            </div>
            <div>
                <label for="bloco">Bloco</label>
                <input type="text" name="bloco" id="bloco" value="<?= htmlspecialchars($bloco) ?>" maxlength="1" required>
                <span><?= htmlspecialchars($erroBloco) ?></span>
            </div>
            <div>
                <label for="sala">Sala</label>
                <input type="number" name="sala" id="sala" value="<?= htmlspecialchars($sala) ?>" required>
                <span><?= htmlspecialchars($erroSala) ?></span>
            </div>
            <div>
                <label for="liberado">Liberado</label>
                <input type="checkbox" name="liberado" id="liberado" <?= $liberado ? 'checked' : '' ?>>
            </div>
            <button type="submit" class="btn-salvar"><?= is_null($id) ? 'Cadastrar' : 'Salvar Alterações' ?></button>
        </form>
    </div>
</body>
</html>
