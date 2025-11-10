<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION["cadastroRestaurante_nome_restaurante"] = $_POST["nome_restaurante"];
    $_SESSION["cadastroRestaurante_telefone"] = $_POST["telefone"];
    $_SESSION["cadastroRestaurante_horarioA"] = $_POST["horario_a"];
    $_SESSION["cadastroRestaurante_horarioF"] = $_POST["horario_f"];

    header("Location: ../cadastroRestaurantePt3.html");
    exit;
} else {
    header("Location: ../cadastroRestaurantePt1.html");
    exit;
}
?>