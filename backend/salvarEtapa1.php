<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION["cadastro_email"] = $_POST["email"];
    $_SESSION["cadastro_senha"] = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    header("Location: ../cadastroClientePt2.html");
    exit;
} else {
    header("Location: ../cadastroClientePt1.html");
    exit;
}
?>