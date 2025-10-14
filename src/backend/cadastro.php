<?php
// backend/cadastro.php
require_once "conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);
    $cpf = trim($_POST["cpf"]);
    $data_nasc = $_POST["data_nasc"];
    $endereco = trim($_POST["endereco"]);
    $cidade = trim($_POST["cidade"]);
    $estado = trim($_POST["estado"]);
    $telefone = trim($_POST["telefone"]);
    $email = trim($_POST["email"]);
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    // verifica se já existe email cadastrado
    $verifica = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $verifica->execute([$email]);

    if ($verifica->rowCount() > 0) {
        echo "Email já cadastrado!";
        exit;
    }

    $sql = "INSERT INTO usuarios (nome, cpf, data_nasc, endereco, cidade, estado, telefone, email, senha)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$nome, $cpf, $data_nasc, $endereco, $cidade, $estado, $telefone, $email, $senha])) {
        header("Location: ../login.html?sucesso=1");
        exit;
    } else {
        echo "Erro ao cadastrar usuário.";
    }
}
?>
