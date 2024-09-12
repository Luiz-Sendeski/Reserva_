<?php 
require_once('includes/sessao.inc.php');
if (!verificaSessao()) {
    header('Location: http://127.0.0.1/IFPR/aula06/login.php');
    exit;
}

 $_SESSION['usuario'] ='';
 session_destroy();
 header('Location: login.php');


?>