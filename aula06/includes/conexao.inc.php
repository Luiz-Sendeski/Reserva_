<?php
try {
    $bancoDados = new PDO(
        'mysql:host=127.0.0.1;port=33306;dbname=ifpr',
        'root',
        '',
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    //echo "Conexão bem sucedida.";
} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}

?>
