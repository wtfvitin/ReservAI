<?php
// Arquivo: alterarSenhaRestaurante.php

// 1. INICIALIZAÇÃO DA SESSÃO E INCLUSÃO DE ARQUIVOS
session_start();
// Usa o caminho correto
include_once 'backend/conexao.php';

// VERIFICAÇÃO DE LOGIN
if (!isset($_SESSION['restaurante_id'])) {
    header("Location: loginRestaurante.html");
    exit;
}

$restaurante_id = $_SESSION['restaurante_id'];
$mensagem = '';
$sucesso = false;

// 2. PROCESSAMENTO DO FORMULÁRIO (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // A. VALIDAÇÃO BÁSICA
    if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
        $mensagem = "Preencha todos os campos.";
    } elseif ($nova_senha !== $confirmar_senha) {
        $mensagem = "A nova senha e a confirmação não coincidem.";
    } elseif (strlen($nova_senha) < 6) {
        $mensagem = "A nova senha deve ter pelo menos 6 caracteres.";
    } else {
        // B. VERIFICAÇÃO DA SENHA ATUAL NO BANCO DE DADOS

        // CORREÇÃO: Usando o nome da coluna "senha"
        $sql = "SELECT senha FROM restaurantes WHERE idrestaurante = ?";

        // ATENÇÃO: Seu SQL usa 'idrestaurante', não 'id'.
        // O código anterior estava usando 'id'. Corrigindo para 'idrestaurante'.
        // Se a sua SESSION usa 'restaurante_id', e a tabela usa 'idrestaurante', 
        // e você está passando $restaurante_id, vamos assumir que o ID está correto.

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$restaurante_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // CORREÇÃO: Pegando o valor da coluna 'senha'
            $hash_senha_bd = $row['senha'];

            // 2. Verifica se a senha fornecida corresponde ao hash no BD
            if (password_verify($senha_atual, $hash_senha_bd)) {

                // C. ATUALIZAÇÃO DA SENHA
                $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                // CORREÇÃO: Usando o nome da coluna "senha"
                $sql_update = "UPDATE restaurantes SET senha = ? WHERE idrestaurante = ?";
                $stmt_update = $pdo->prepare($sql_update);

                // Executa a atualização com os novos parâmetros
                if ($stmt_update->execute([$novo_hash, $restaurante_id])) {
                    $mensagem = "Senha alterada com sucesso!";
                    $sucesso = true;
                } else {
                    $mensagem = "Erro ao atualizar a senha.";
                }
            } else {
                $mensagem = "A senha atual está incorreta.";
            }
        } else {
            $mensagem = "Erro: Usuário não encontrado no sistema.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/Logo.png">

    <link rel="stylesheet" href="src/css/padrão.css">
    <link rel="stylesheet" href="src/css/navbar.css">
    <link rel="stylesheet" href="src/css/alterarSenhaRestaurante.css">

    <title>Alterar Senha - ReservAI</title>

</head>

<body>
    <div class="barra-topo">
        <div class="esquerda">
            <img src="img/Icone Voltar.png" alt="Voltar" id="voltar" class="icone-voltar">
            <h1>Alterar Senha</h1>
        </div>
    </div>

    <main class="config-container">

        <div class="form-container">
            <h2>Atualize sua Senha</h2>
            <p>Para sua segurança, digite a senha atual e a nova senha.</p>

            <?php if (!empty($mensagem)): ?>
                <div class="mensagem <?php echo $sucesso ? 'sucesso' : 'erro'; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="alterarSenhaRestaurante.php">

                <div class="form-group">
                    <input type="password" id="senha_atual" name="senha_atual" required placeholder="Senha Atual">
                    <span class="password-toggle" onclick="togglePasswordVisibility('senha_atual', this)">
                        <img src="img/Icone Olho Fechado.png" alt="Mostrar Senha">
                    </span>
                </div>

                <div class="form-group">
                    <input type="password" id="nova_senha" name="nova_senha" required placeholder="Nova Senha">
                    <span class="password-toggle" onclick="togglePasswordVisibility('nova_senha', this)">
                        <img src="img/Icone Olho Fechado.png" alt="Mostrar Senha">
                    </span>
                </div>

                <div class="form-group">
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required placeholder="Confirme a Nova Senha">
                    <span class="password-toggle" onclick="togglePasswordVisibility('confirmar_senha', this)">
                        <img src="img/Icone Olho Fechado.png" alt="Mostrar Senha">
                    </span>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Salvar Nova Senha</button>
            </form>
        </div>

    </main>

    <div class="overlay" id="overlay"></div>

    <nav class="navbar">
        <a href="indexRestaurante.php" class="desativo-hover">
            <img src="img/Icone Casa.png" class="img-nav" alt="Home">
        </a>

        <a href="configuracoesRestaurante.html" class="desativo-hover">
            <img src="img/Icone Configurações.png" class="img-nav" alt="Configurações">
        </a>

        <a href="perfilGestor.php" class="desativo-hover">
            <img src="img/Icone Perfil.png" class="img-nav" alt="Perfil">
        </a>

    </nav>

</body>
<script>
    /*============================================================== 
 BOTÃO VOLTAR
 ==============================================================*/
    document.getElementById('voltar').addEventListener('click', function() {
        history.back();
    });

    /*
    FUNÇÃO PARA MOSTRAR/ESCONDER SENHA
    */
    function togglePasswordVisibility(idInput, toggleElement) {
        const input = document.getElementById(idInput);
        const icon = toggleElement.querySelector('img');

        if (input.type === "password") {
            input.type = "text";
            // Altera o ícone para o olho aberto (assumindo que você tenha um Icone Olho Aberto.png)
            icon.src = "img/Icone Olho Aberto.png";
            icon.alt = "Esconder Senha";
        } else {
            input.type = "password";
            icon.src = "img/Icone Olho Fechado.png";
            icon.alt = "Mostrar Senha";
        }
    }
</script>

</html>