<?php
// Inicia a sessão somente se ainda não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Função para verificar se a sessão é válida
function verificaSessao() {
    if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
        return false;
    }
    $tipo_usuario = verificaTipoUsuario();
    $_SESSION['is_admin'] = ($tipo_usuario === 'a');
    return true;
}

// Função para verificar o tipo de usuário
function verificaTipoUsuario() {
    if (isset($_SESSION['usuario'])) {
        $usuario = unserialize(base64_decode($_SESSION['usuario']));
        if (isset($usuario['tipo'])) {
            return $usuario['tipo'];  // Retorna 'a' para administrador ou 'n' para normal
        }
    }
    return false;  // Retorna falso se o tipo não estiver definido
}

// Função para proteger a sessão contra sequestro (session hijacking)
function protegeSessao() {
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id();
        $_SESSION['initiated'] = true;
    }
}

// Aplica proteção básica à sessão
protegeSessao();
?>
