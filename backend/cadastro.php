<?php
require_once "conexao.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Recupera os dados armazenados nas etapas anteriores
    $email = $_SESSION["cadastro_email"] ?? null;
    $senha = $_SESSION["cadastro_senha"] ?? null;
    $nome = $_SESSION["cadastro_nome"] ?? null;
    $sobrenome = $_SESSION["cadastro_sobrenome"] ?? null;
    $cpf = $_SESSION["cadastro_cpf"] ?? null;
    $data_nasc = $_SESSION["cadastro_data_nasc"] ?? null;
    $telefone = $_SESSION["cadastro_telefone"] ?? null;

    // Dados da etapa 3 (endereço)
    $cep = trim($_POST["cep"]);
    $endereco = trim($_POST["endereco"]);
    $cidade = trim($_POST["cidade"]);
    $estado = trim($_POST["estado"]);

    if (!$email || !$senha || !$nome) {
        die("Erro: dados incompletos. Refaça o cadastro.");
    }

    // Verifica se já existe e-mail
    $check = $pdo->prepare("SELECT idcliente FROM clientes WHERE email_cli = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        echo "E-mail já cadastrado!";
        exit;
    }

    // Insere o usuário
    $sql = "INSERT INTO clientes 
            (nome_cli, sobrenome_cli, cpf_cli, telefone_cli, email_cli, senha, dtNasc_cli, cep_cli, endereco_rua_cli, endereco_cidade_cli, endereco_estado_cli)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$nome, $sobrenome, $cpf, $telefone, $email, $senha, $data_nasc, $cep, $endereco, $cidade, $estado])) {
        session_unset();
        session_destroy();
        header("Location: ../loginCliente.html?sucesso=1");
        exit;
    } else {
        echo "Erro ao cadastrar.";
    }
}
?>
