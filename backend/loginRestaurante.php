<?php
// =================================================================
// SCRIPT DE PROCESSAMENTO DE LOGIN PARA GESTORES DE RESTAURANTE
// =================================================================

// 1. INICIA A SESSÃO e Carrega a Conexão
// O caminho deve ser ajustado para onde o arquivo de conexão realmente está
require_once "conexao.php";
session_start();

// 2. Verifica o Método da Requisição
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Redireciona de volta em caso de acesso direto ou método incorreto
    header("Location: ../loginRestaurante.html");
    exit;
}

// 3. Captura e Limpa os Dados do Formulário
$email = trim($_POST["email"] ?? "");
$senha = trim($_POST["senha"] ?? "");

// 4. Validação de Campos Vazios
if ($email === "" || $senha === "") {
    header("Location: ../loginRestaurante.html?erro=vazio");
    exit;
}

// 5. Prepara e Executa a Busca por Email
// Busca o hash da senha e dados essenciais do restaurante
$sql = "SELECT idrestaurante, nome_restaurante, senha FROM restaurantes WHERE email_restaurante = :email";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $gestor = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Em caso de erro de banco de dados, retorna para login com erro genérico.
    header("Location: ../loginRestaurante.html?erro=db");
    // Opcional: Logar o erro completo $e->getMessage() para debug
    exit;
}


if ($gestor) {
    // 6. Verifica a Senha (Hashing)
    // Compara a senha digitada com o hash armazenado no banco.
    if (password_verify($senha, $gestor["senha"])) {

        // Login bem-sucedido: Armazena dados na sessão
        $_SESSION["restaurante_id"]  = $gestor["idrestaurante"];
        $_SESSION["restaurante_nome"] = $gestor["nome_restaurante"];
        $_SESSION["restaurante_email"] = $email;
        $_SESSION["tipo"] = "gestor"; // Define o tipo de usuário

        // Redireciona para a página principal do gestor
        header("Location: ../indexRestaurante.php");
        exit;
    }
}

// 7. Falha no Login
// Redireciona para o login com erro de credenciais inválidas se:
// - O gestor não foi encontrado ($gestor é false).
// - A senha não corresponde ao hash (password_verify falhou).
header("Location: ../loginRestaurante.html?erro=credenciais");
exit;
