<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION["cadastro_nome"] = $_POST["nome"];
    $_SESSION["cadastro_sobrenome"] = $_POST["sobrenome"];
    $_SESSION["cadastro_cpf"] = $_POST["cpf"];
    $_SESSION["cadastro_data_nasc"] = $_POST["data_nasc"];
    $_SESSION["cadastro_telefone"] = $_POST["telefone"];

    header("Location: ../cadastroClientePt3.html");
    exit;
} else {
    header("Location: ../cadastroClientePt2.html");
    exit;
}
?>