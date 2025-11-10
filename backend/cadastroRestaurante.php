<?php
require_once "conexao.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Recupera os dados salvos nas etapas anteriores
    $email              = $_SESSION["cadastroRestaurante_email"]                ?? null;
    $senha              = $_SESSION["cadastroRestaurante_senha"]                ?? null;
    $nomeRestaurante    = $_SESSION["cadastroRestaurante_nome_restaurante"]     ?? null;
    $telefone           = $_SESSION["cadastroRestaurante_telefone"]             ?? null;
    $horario_a          = $_SESSION["cadastroRestaurante_horarioA"]             ?? null;
    $horario_f          = $_SESSION["cadastroRestaurante_horarioF"]             ?? null;

    // Dados da última etapa (endereço)
    $cep      = trim($_POST["cep"]);
    $endereco = trim($_POST["endereco"]);
    $numero = trim($_POST["numero"]);
    $bairro = trim($_POST["bairro"]);
    $cidade   = trim($_POST["cidade"]);
    $estado   = trim($_POST["estado"]);

    // Verificação de segurança
    if (!$email || !$senha || !$nomeRestaurante || !$telefone || !$horario_a || !$horario_f || !$cep || !$endereco || !$numero || !$bairro || !$cidade || !$estado) {
        die("Erro: dados incompletos. Refaça o cadastro.");
    }

    // Verifica se o email já está cadastrado
    $check = $pdo->prepare("SELECT idcliente FROM clientes WHERE email_cli = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        echo "E-mail já cadastrado!";
        exit;
    }

    // Criptografa a senha antes de salvar
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Query de inserção
    $sql = "INSERT INTO restaurantes 
            (nome_restaurante, telefone, email_restaurante, horario_abertura, horario_fechamento, cep_res, endereco_rua_res, 
            endereco_num_res, endereco_bairro_res, endereco_cidade_res, endereco_estado_res, senha)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    $ok = $stmt->execute([
        $nomeRestaurante,
        $telefone,
        $email,
        $horario_a,
        $horario_f,
        $cep,
        $endereco,
        $numero,
        $bairro,
        $cidade,
        $estado,
        $senha
    ]);

    if ($ok) {
        session_unset();
        session_destroy();
        header("Location: ../loginCliente.html?sucesso=1");
        exit;
    } else {
        echo "Erro ao cadastrar. Tente novamente.";
    }
}
