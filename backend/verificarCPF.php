<?php
require_once "conexao.php";

if (!isset($_GET["cpf"])) {
    echo "erro";
    exit;
}

$cpf = $_GET["cpf"];

$stmt = $pdo->prepare("SELECT idcliente FROM clientes WHERE cpf_cli = ?");
$stmt->execute([$cpf]);

echo $stmt->rowCount() > 0 ? "existe" : "ok";
