<?php
// ================================
// 1. INICIA A SESSÃO
// ================================
session_start();
require_once "conexao.php";

// ================================
// VERIFICAR SE O FORMULÁRIO FOI ENVIADO
// ================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Campos vindo do formulário de reserva
    $restaurante_id = $_POST["restaurante_id"] ?? null; 
    $numero_clientes = $_POST["pessoas"] ?? null;
    $data = $_POST["data"] ?? null;
    $horario_inicio = $_POST["horario"] ?? null;

    // ================================
    // DEFINIR CLIENTE E VERIFICAR LOGIN
    // ================================
    // Verifica se o cliente está logado. Se não, redireciona.
    if (empty($_SESSION['usuario_id'])) {
        header("Location: ../cadastroClientePt1.php");
        exit;
    }
    // CAPTURA O ID DO CLIENTE LOGADO CORRETAMENTE DA SESSÃO
    $cliente_id = $_SESSION['usuario_id']; 


    // ================================
    // VALIDAÇÃO BÁSICA
    // ================================
    if (empty($restaurante_id) || empty($numero_clientes) || empty($data) || empty($horario_inicio)) {
        die("Erro: Preencha todos os campos obrigatórios e o ID do restaurante.");
    }

    // Garante que o ID do restaurante seja um número inteiro seguro
    $restaurante_id = (int)$restaurante_id;
    
    // Converte a quantidade de pessoas para inteiro
    $numero_clientes = intval($numero_clientes); 

    // ================================
    // DEFINIR HORÁRIO FINAL (ex: reserva dura 1h30)
    // ================================
    $horario_fim = date("H:i", strtotime($horario_inicio . " + 1 hour + 30 minutes"));

    // $restaurante_id JÁ ESTÁ DEFINIDO ACIMA usando o valor vindo do formulário
    $mesa_id = null;         // Pode ser definida automaticamente futuramente

    // ================================
    // INSERIR NO BANCO DE DADOS
    // ================================
    $sql = "INSERT INTO reservas 
             (cliente_id, numero_clientes, mesa_id, restaurante_id, data_reserva, horario_inicio, horario_fim, status)
             VALUES (:cliente_id, :numero_clientes, :mesa_id, :restaurante_id, :data_reserva, :horario_inicio, :horario_fim, 'confirmada')";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":cliente_id"       => $cliente_id,          // AGORA USA O ID DA SESSÃO
            ":numero_clientes"  => $numero_clientes,
            ":mesa_id"          => $mesa_id,
            ":restaurante_id"   => $restaurante_id,
            ":data_reserva"     => $data,
            ":horario_inicio"   => $horario_inicio,
            ":horario_fim"      => $horario_fim
        ]);

        // Redirecionar para página de sucesso
        header("Location: ../reserva_confirmada.php?sucesso=1");
        exit;

    } catch (PDOException $e) {
        die("Erro ao criar reserva: " . $e->getMessage());
    }
} else {
    echo "Requisição inválida.";
}
?>