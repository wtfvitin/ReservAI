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

$stmt = $pdo->prepare("SELECT * FROM restaurantes WHERE email_restaurante = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() === 1) {
    $gestor = $stmt->fetch(PDO::FETCH_ASSOC);

    // verifica a hash
    if (password_verify($senha, $gestor["senha"])) {

        $_SESSION["usuario_id"]    = $gestor["idrestaurante"];
        $_SESSION["usuario_nome"]  = $gestor["nome_restaurante"];
        $_SESSION["usuario_email"] = $gestor["email_restaurante"];
        $_SESSION["tipo"]          = "gestor";

        header("Location: ../indexRestaurante.php");
        exit;
    }

    exit("Email ou senha incorretos.");
}

// se não encontrou
exit("Email ou senha incorretos.");
