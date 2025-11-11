<?php
require_once "conexao.php";

if (!isset($_GET["email"])) {
    echo "erro";
    exit;
}

$email = $_GET["email"];

$stmt = $pdo->prepare("SELECT idcliente FROM clientes WHERE email_cli = ?");
$stmt->execute([$email]);

echo $stmt->rowCount() > 0 ? "existe" : "ok";
