<?php
// ================================
// 1. INICIA A SESSÃO
// ================================
session_start();
// O caminho deve ser ajustado para onde o arquivo de conexão realmente está
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
        // Redireciona o usuário para a página de login/cadastro se não estiver logado.
        header("Location: ../cadastroClientePt1.php");
        exit;
    }
    // CAPTURA O ID DO CLIENTE LOGADO CORRETAMENTE DA SESSÃO
    $cliente_id = $_SESSION['usuario_id'];


    // ================================
    // VALIDAÇÃO BÁSICA
    // ================================
    if (empty($restaurante_id) || empty($numero_clientes) || empty($data) || empty($horario_inicio)) {
        // Em um ambiente de produção, seria melhor redirecionar ou mostrar um erro mais amigável.
        die("Erro: Preencha todos os campos obrigatórios e o ID do restaurante.");
    }

    // Garante que o ID do restaurante seja um número inteiro seguro
    $restaurante_id = (int)$restaurante_id;

    // Converte a quantidade de pessoas para inteiro
    $numero_clientes = intval($numero_clientes);

    // ================================
    // 🚨 ATUALIZAÇÃO 1: CALCULAR HORÁRIO FINAL (2 horas de duração)
    // ================================
    // Assumindo que a duração da reserva é de 2 horas.
    $horario_fim = date("H:i:s", strtotime($horario_inicio . " + 2 hours"));


    // ================================
    // ❌ REMOVIDO: Lógica para gerar $mesa_id aleatória foi removida
    // ================================
    // A coluna 'mesa_id' não existe na tabela 'reservas', então removemos
    // a variável e seu uso.

    // ================================
    // INSERIR NO BANCO DE DADOS
    // ================================
    $sql = "INSERT INTO reservas 
      (cliente_id, numero_clientes, restaurante_id, data_reserva, horario_inicio, horario_fim, status)
      VALUES (:cliente_id, :numero_clientes, :restaurante_id, :data_reserva, :horario_inicio, :horario_fim, 'confirmada')";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":cliente_id"      => $cliente_id,
            ":numero_clientes" => $numero_clientes,
            ":restaurante_id"  => $restaurante_id,
            ":data_reserva"    => $data,
            ":horario_inicio"  => $horario_inicio,
            ":horario_fim"     => $horario_fim
        ]);

        // Redirecionar para a agenda (ou página de confirmação)
        header("Location: ../reserva_confirmada.php?sucesso=reserva_criada");
        exit;
    } catch (PDOException $e) {
        // Se houver um erro, imprime a mensagem para debug.
        die("Erro ao criar reserva: " . $e->getMessage());
    }
} else {
    echo "Requisição inválida.";
}