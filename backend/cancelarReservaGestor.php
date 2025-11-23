<?php
session_start();
require_once "conexao.php"; // Ajuste o caminho conforme sua estrutura

// 1. Verifica se o gestor está logado
if (empty($_SESSION['restaurante_id'])) {
    // Redireciona para o login se não estiver logado
    header("Location: ../loginRestaurante.php");
    exit;
}

// 2. Verifica se a requisição é POST e se a ID da reserva foi enviada
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_reserva'])) {
    $id_reserva = filter_input(INPUT_POST, 'id_reserva', FILTER_VALIDATE_INT);
    $restaurante_id = $_SESSION['restaurante_id'];

    if ($id_reserva === false || $id_reserva === null) {
        // ID de reserva inválida
        header("Location: ../indexRestaurante.php?erro=ID de reserva inválida.");
        exit;
    }

    try {
        // 3. SQL para cancelar a reserva
        // Usa o restaurante_id para garantir que o gestor só cancele suas próprias reservas
        $sql_cancelar = "
            UPDATE reservas
            SET status = 'cancelada'
            WHERE idreserva = :id_reserva 
            AND restaurante_id = :restaurante_id
            AND status = 'confirmada'
        ";

        $stmt = $pdo->prepare($sql_cancelar);
        $stmt->bindParam(':id_reserva', $id_reserva, PDO::PARAM_INT);
        $stmt->bindParam(':restaurante_id', $restaurante_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                // Sucesso no cancelamento
                header("Location: ../indexRestaurante.php?sucesso=Reserva cancelada com sucesso!");
            } else {
                // Nenhuma linha afetada: reserva não encontrada ou já cancelada
                header("Location: ../indexRestaurante.php?erro=Reserva não encontrada ou já foi cancelada.");
            }
        } else {
            // Erro na execução
            header("Location: ../indexRestaurante.php?erro=Erro ao cancelar a reserva no banco de dados.");
        }
        exit;

    } catch (PDOException $e) {
        error_log("Erro no cancelamento da reserva: " . $e->getMessage());
        header("Location: ../indexRestaurante.php?erro=Erro interno. Tente novamente mais tarde.");
        exit;
    }
} else {
    // Se não for POST ou faltar a ID
    header("Location: ../indexRestaurante.php");
    exit;
}
?>