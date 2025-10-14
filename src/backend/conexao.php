<?php
// backend/conexao.php
$host = "localhost";
$dbname = "reservai";
$user = "root";  // ajuste conforme seu ambiente
$pass = "";      // senha do banco (se tiver)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>
