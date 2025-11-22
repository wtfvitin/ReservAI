<?php
require_once "conexao.php";

// ================================
// VERIFICAR SE O FORMULÁRIO FOI ENVIADO
// ================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Campos vindo do formulário de reserva
    $numero_clientes = $_POST["pessoas"] ?? null;
    $data = $_POST["data"] ?? null;
    $horario_inicio = $_POST["horario"] ?? null;

    // ================================
    // VALIDAÇÃO BÁSICA
    // ================================
    if (empty($numero_clientes) || empty($data) || empty($horario_inicio)) {
        die("Erro: Preencha todos os campos obrigatórios.");
    }

    // converter "3 Pessoas" → apenas número
    $numero_clientes = intval($numero_clientes);

    // ================================
    // DEFINIR HORÁRIO FINAL (ex: reserva dura 1h30)
    // ================================
    $horario_fim = date("H:i", strtotime($horario_inicio . " + 1 hour + 30 minutes"));

    // ================================
    // DEFINIR CLIENTE E RESTAURANTE
    // (aqui você pode pegar da sessão futuramente)
    // ================================
    $cliente_id = 1;        // TEMPORÁRIO – substituir quando houver login
    $restaurante_id = 1;    // TEMPORÁRIO – gerencia somente um restaurante?
    $mesa_id = null;        // Pode ser definida automaticamente futuramente

    // ================================
    // INSERIR NO BANCO DE DADOS
    // ================================
    $sql = "INSERT INTO reservas 
            (cliente_id, numero_clientes, mesa_id, restaurante_id, data_reserva, horario_inicio, horario_fim, status)
            VALUES (:cliente_id, :numero_clientes, :mesa_id, :restaurante_id, :data_reserva, :horario_inicio, :horario_fim, 'confirmada')";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":cliente_id"       => $cliente_id,
            ":numero_clientes"  => $numero_clientes,
            ":mesa_id"          => $mesa_id,
            ":restaurante_id"   => $restaurante_id,
            ":data_reserva"     => $data,
            ":horario_inicio"   => $horario_inicio,
            ":horario_fim"      => $horario_fim
        ]);

        // Redirecionar para página de sucesso
        header("Location: reserva_confirmada.php?sucesso=1");
        exit;

    } catch (PDOException $e) {
        die("Erro ao criar reserva: " . $e->getMessage());
    }
} else {
    echo "Requisição inválida.";
}
?>