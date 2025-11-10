<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION["cadastroRestaurante_email"] = $_POST["email"];
    $_SESSION["cadastroRestaurante_senha"] = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    header("Location: ../cadastroRestaurantePt2.html");
    exit;
} else {
    header("Location: ../cadastroRestaurantePt1.html");
    exit;
}
?>