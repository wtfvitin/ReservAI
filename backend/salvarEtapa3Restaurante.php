<?php
require_once "conexao.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION["cadastroRestaurante_cep"] = $_POST["cep"];
    $_SESSION["cadastroRestaurante_endereco"] = $_POST["endereco"];
    $_SESSION["cadastroRestaurante_numero"] = $_POST["numero"];
    $_SESSION["cadastroRestaurante_bairro"] = $_POST["bairro"];
    $_SESSION["cadastroRestaurante_cidade"] = $_POST["cidade"];
    $_SESSION["cadastroRestaurante_estado"] = $_POST["estado"];

    header("Location: ../personalizarRestaurantePt1.html");
    exit;
} else {
    header("Location: ../cadastroRestaurantePt1.html");
    exit;
}
