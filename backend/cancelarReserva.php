<?php
// ================================
// 1. INICIA A SESSÃO E CONEXÃO
// ================================
session_start();
require_once "conexao.php";

// Verifica se o ID do cliente está na sessão e se a requisição é válida
if (empty($_SESSION['usuario_id']) || empty($_GET['id'])) {
    // Redireciona ou exibe erro se faltar login ou ID da reserva
    header("Location: ../agenda.php");
    exit;
}

$cliente_id = $_SESSION['usuario_id'];
$idreserva = (int)$_GET['id'];

// ================================
// 2. TENTA CANCELAR A RESERVA
// ================================
$sql = "UPDATE reservas 
        SET status = 'cancelada' 
        WHERE idreserva = :idreserva 
        AND cliente_id = :cliente_id"; // GARANTE QUE SÓ O PRÓPRIO CLIENTE POSSA CANCELAR

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":idreserva" => $idreserva,
        ":cliente_id" => $cliente_id
    ]);

    // Verifica se alguma linha foi afetada (se a reserva existia e pertencia ao cliente)
    if ($stmt->rowCount() > 0) {
        // Redireciona para a agenda com uma mensagem de sucesso (ou apenas recarrega)
        header("Location: ../agenda.php?cancelamento=sucesso");
        exit;
    } else {
        // Reserva não encontrada ou não pertence ao usuário
        header("Location: ../agenda.php?cancelamento=erro&motivo=nao_encontrado");
        exit;
    }

} catch (PDOException $e) {
    // Erro no banco de dados
    error_log("Erro ao cancelar reserva: " . $e->getMessage());
    header("Location: ../agenda.php?cancelamento=erro&motivo=db");
    exit;
}
?>