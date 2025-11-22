<?php
// Inicia a sessão (necessário para acessar as variáveis de sessão, mesmo que seja para destruí-las)
session_start();

// Destrói todas as variáveis de sessão
$_SESSION = array();

// Se o cookie de sessão existir, destrói-o. 
// Isso garante que o navegador esqueça a sessão.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrói a sessão
session_destroy();

// Redireciona o usuário para a página de login ou para a página inicial.
// Neste caso, vamos simular o redirecionamento para uma página inicial (index.php).
// Mantenha 'index.php' como o destino padrão.
header("Location: ../index.php");
exit;
?>