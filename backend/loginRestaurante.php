<?php
// CORREÇÃO DE CAMINHO: Usar "../conexao.php" se 'conexao.php' estiver na pasta raiz e o loginRestaurante.php em 'backend/'.
// Ajuste este caminho se a localização do seu arquivo de conexão for diferente.
require_once "conexao.php"; 
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Redireciona de volta em caso de acesso direto ou método incorreto
    header("Location: ../loginRestaurante.html");
    exit;
}

$email = trim($_POST["email"] ?? "");
$senha = trim($_POST["senha"] ?? "");

// Se algum campo estiver vazio, redireciona de volta
if ($email === "" || $senha === "") {
    header("Location: ../loginRestaurante.html?erro=vazio");
    exit;
}

// 1. Prepara a busca por email, buscando apenas os campos essenciais
$stmt = $pdo->prepare("SELECT idrestaurante, nome_restaurante, senha FROM restaurantes WHERE email_restaurante = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();

$gestor = $stmt->fetch(PDO::FETCH_ASSOC);

if ($gestor) {
    // 2. Verifica a hash
    if (password_verify($senha, $gestor["senha"])) {

        $_SESSION["restaurante_id"]    = $gestor["idrestaurante"]; 
        $_SESSION["restaurante_nome"]  = $gestor["nome_restaurante"];
        $_SESSION["restaurante_email"] = $email; // Usar o email sanitizado
        $_SESSION["tipo"]              = "gestor";

        // Login bem-sucedido
        header("Location: ../indexRestaurante.php");
        exit;
    }
}

// Se não encontrou o gestor OU a senha estiver incorreta
header("Location: ../loginRestaurante.html?erro=credenciais");
exit;