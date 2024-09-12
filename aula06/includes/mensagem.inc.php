<?php
        if(isset($_SESSION['mensagem_sucesso']) && !empty($_SESSION['mensagem_sucesso'])) {
            echo '<div class="mensagem_sucesso">' . htmlspecialchars($_SESSION['mensagem_sucesso']) . '</div>';
            $_SESSION['mensagem_sucesso'] ='';
        }
        if(isset($_SESSION['mensagem_erro']) && !empty($_SESSION['mensagem_erro'])) {
            echo '<div class="mensagem_erro">' . htmlspecialchars($_SESSION['mensagem_erro']) . '</div>';
            $_SESSION['mensagem_erro'] ='';
        }

?>
