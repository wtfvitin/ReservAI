<?php
require_once "conexao.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];

    // Busca o cliente pelo e-mail
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email_cli = ?");
    $stmt->execute([$email]);

    // Verifica se encontrou
    if ($stmt->rowCount() === 1) {
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica senha
        if (password_verify($senha, $cliente["senha"])) {

            // Cria sessão do usuário logado
            $_SESSION["cliente_id"] = $cliente["idcliente"];
            $_SESSION["cliente_nome"] = $cliente["nome_cli"];
            $_SESSION["cliente_email"] = $cliente["email_cli"];

            // Redireciona para a home
            header("Location: ../index.php");
            exit;

        } else {
            echo "Senha incorreta!";
            exit;
        }

    } else {
        echo "Usuário não encontrado!";
        exit;
    }
}
?>
