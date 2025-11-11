<?php
require_once "conexao.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Método inválido.");
}

// --- Dados gravados nas etapas anteriores ---
$email      = $_SESSION["cadastro_email"]      ?? null;
$senha      = $_SESSION["cadastro_senha"]      ?? null;
$nome       = $_SESSION["cadastro_nome"]       ?? null;
$sobrenome  = $_SESSION["cadastro_sobrenome"]  ?? null;
$cpf        = $_SESSION["cadastro_cpf"]        ?? null;
$data_nasc  = $_SESSION["cadastro_data_nasc"]  ?? null;
$telefone   = $_SESSION["cadastro_telefone"]   ?? null;

// Dados de endereço
$cep      = trim($_POST["cep"] ?? "");
$endereco = trim($_POST["endereco"] ?? "");
$cidade   = trim($_POST["cidade"] ?? "");
$estado   = trim($_POST["estado"] ?? "");

// --- Verificação básica ---
if (!$email || !$senha || !$nome) {
    exit("Erro: dados incompletos. Refaça o cadastro.");
}

// --- Verifica se o email já existe ---
$check = $pdo->prepare("SELECT idcliente FROM clientes WHERE email_cli = ?");
$check->execute([$email]);

if ($check->rowCount() > 0) {
    exit("E-mail já cadastrado!");
}

// --- Inserção (sem hash) ---
$sql = "INSERT INTO clientes 
        (nome_cli, sobrenome_cli, cpf_cli, telefone_cli, email_cli, senha, dtNasc_cli, 
         cep_cli, endereco_rua_cli, endereco_cidade_cli, endereco_estado_cli)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);

$ok = $stmt->execute([
    $nome,
    $sobrenome,
    $cpf,
    $telefone,
    $email,
    $senha,     // <--- sem hash
    $data_nasc,
    $cep,
    $endereco,
    $cidade,
    $estado
]);

if ($ok) {
    session_unset();
    session_destroy();
    header("Location: ../login.html?sucesso=1");
    exit;
}

exit("Erro ao cadastrar. Tente novamente.");
