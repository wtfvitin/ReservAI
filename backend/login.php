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

/* ============================================
   1) LOGIN COMO GESTOR
   ============================================ */
$stmt = $pdo->prepare("SELECT * FROM restaurantes WHERE email_restaurante = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() === 1) {
    $gestor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($senha === $gestor["senha"]) {   // <--- sem hash

        $_SESSION["usuario_id"]    = $gestor["idrestaurante"];
        $_SESSION["usuario_nome"]  = $gestor["nome_restaurante"];
        $_SESSION["usuario_email"] = $gestor["email_restaurante"];
        $_SESSION["tipo"]          = "gestor";

        header("Location: ../indexRestaurante.php");
        exit;
    }

    exit("Email ou senha incorretos.");
}

/* ============================================
   2) LOGIN COMO CLIENTE
   ============================================ */
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE email_cli = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() === 1) {
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($senha === $cliente["senha"]) {    // <--- sem hash

        $_SESSION["usuario_id"]    = $cliente["idcliente"];
        $_SESSION["usuario_nome"]  = $cliente["nome_cli"];
        $_SESSION["usuario_email"] = $cliente["email_cli"];
        $_SESSION["tipo"]          = "cliente";

        header("Location: ../index.php");
        exit;
    }

    exit("Email ou senha incorretos.");
}

// Nenhum usuário encontrado
exit("Email ou senha incorretos.");
