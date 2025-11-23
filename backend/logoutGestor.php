<?php
// Arquivo: logoutGestor.php

// 1. Inicia a sessão para acessar os dados da sessão atual (obrigatório)
session_start();

// 2. Destrói todas as variáveis de sessão registradas
$_SESSION = array();

// 3. Se desejar matar completamente a sessão (opcional, mas recomendado)
// Nota: Isso também apagará o cookie de sessão.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finalmente, destrói a sessão
session_destroy();

// 5. Redireciona o usuário para a página de login do restaurante
header("Location: ../loginRestaurante.html");
exit;
?>