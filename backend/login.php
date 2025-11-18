<?php
require_once "conexao.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Método inválido.");
}

$email = trim($_POST["email"] ?? "");
$senha = trim($_POST["senha"] ?? "");

if ($email === "" || $senha === "") {
    exit("Preencha todos os campos.");
}

$stmt = $pdo->prepare("SELECT * FROM clientes WHERE email_cli = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() === 1) {

    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Aqui usamos password_verify porque a senha está com hash no banco
    if (password_verify($senha, $cliente["senha"])) {

        $_SESSION["usuario_id"]    = $cliente["idcliente"];
        $_SESSION["usuario_nome"]  = $cliente["nome_cli"];
        $_SESSION["usuario_email"] = $cliente["email_cli"];
        $_SESSION["tipo"]          = "cliente";

        header("Location: ../index.php");
        exit;
    }

    exit("Email ou senha incorretos.");
}

// Email não existe
exit("Email ou senha incorretos.");
