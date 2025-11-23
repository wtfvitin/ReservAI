<?php
// Arquivo: adicionarItemCardapio.php

session_start();
include_once 'backend/conexao.php'; // Inclui a conexão PDO

// VERIFICAÇÃO DE LOGIN E ID DO RESTAURANTE
if (!isset($_SESSION['restaurante_id'])) {
    header("Location: loginRestaurante.html");
    exit;
}

$restaurante_id = $_SESSION['restaurante_id'];
$mensagem = '';
$erro_upload = false;

// 1. PROCESSAMENTO DO FORMULÁRIO (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_prato = trim($_POST['nome_prato'] ?? '');
    $preco_str = str_replace(',', '.', $_POST['preco_comida'] ?? '0.00');
    $preco = (float) $preco_str;

    // Supondo que você queira uma descrição simples (adicionada ao formulário abaixo)
    $descricao = trim($_POST['descricao_prato'] ?? '');

    // Variável para a imagem
    $conteudo_imagem = null;

    // --- Validação da Imagem ---
    if (isset($_FILES['imagem_comida']) && $_FILES['imagem_comida']['error'] === UPLOAD_ERR_OK) {
        $upload_file = $_FILES['imagem_comida'];

        // Verifica o tipo MIME (opcional, mas recomendado)
        $tipo_permitido = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($upload_file['type'], $tipo_permitido)) {
            $mensagem = "Erro: Tipo de arquivo não permitido. Use JPEG, PNG ou WEBP.";
            $erro_upload = true;
        } else {
            // Lê o conteúdo da imagem (BLOB)
            $conteudo_imagem = file_get_contents($upload_file['tmp_name']);

            if ($conteudo_imagem === false) {
                $mensagem = "Erro ao ler o conteúdo da imagem.";
                $erro_upload = true;
            }
        }
    } else {
        $mensagem = "Erro no upload da imagem ou imagem não selecionada.";
        $erro_upload = true;
    }

    // --- Validação dos Dados e Inserção no BD ---
    if (!$erro_upload && !empty($nome_prato) && $preco > 0) {
        try {
            // Prepara a query de inserção (adicionando a descrição no futuro, se a tabela mudar)
            // Por enquanto, usamos as colunas atuais: restaurante_id, nome_alimento, foto_alimento, preco
            $sql_insert = "INSERT INTO cardapio (restaurante_id, nome_alimento, foto_alimento, preco) VALUES (?, ?, ?, ?)";
            $stmt_insert = $pdo->prepare($sql_insert);

            // Bind dos parâmetros
            $stmt_insert->bindParam(1, $restaurante_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(2, $nome_prato, PDO::PARAM_STR);
            $stmt_insert->bindParam(3, $conteudo_imagem, PDO::PARAM_LOB); // Bind para BLOB
            $stmt_insert->bindParam(4, $preco);

            if ($stmt_insert->execute()) {
                // Redireciona para a tela de gerenciamento com status de sucesso
                header("Location: gerenciarCardapio.php?status=adicionado");
                exit();
            } else {
                $mensagem = "Erro ao inserir o item no banco de dados.";
            }
        } catch (\PDOException $e) {
            $mensagem = "Erro de Banco de Dados: " . $e->getMessage();
        }
    } elseif (!$erro_upload) {
        // Erro de validação de campo
        $mensagem = "Preencha o nome do prato e o preço corretamente.";
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
    <link rel="stylesheet" href="src/css/adicionarItemCardapio.css">
    <link rel="stylesheet" href="src/css/personalizarRestaurantePt2.css">
    <title>Adicionar Item - ReservAI</title>

</head>

<body>
    <div class="barra-topo">
        <div class="esquerda">
            <img src="img/Icone Voltar.png" alt="Voltar" id="voltar" class="icone-voltar">
            <h1>Adicionar Prato</h1>
        </div>
    </div>

    <main>
        <div class="container">

            <?php if (!empty($mensagem)): ?>
                <div class="mensagem erro">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="adicionarItemCardapio.php" enctype="multipart/form-data">

                <div class="custom-file-upload">
                    <label for="imagem_comida" id="label-botao-imagem">
                        Escolher Imagem
                    </label>
                    <input type="file" id="imagem_comida"
                        name="imagem_comida" accept="image/jpeg, image/png, image/webp" required>
                    <span id="nome-arquivo-imagem">Foto do Prato</span>
                </div>

                <input type="text" class="input-preco"
                    name="nome_prato"
                    placeholder="Nome do Prato"
                    required
                    maxlength="100">

                <input type="text" class="input-preco"
                    name="preco_comida"
                    placeholder="Preço (Ex: 35.90)"
                    required
                    pattern="[0-9]+([,\.][0-9]{1,2})?">

                <button type="submit" class="btn-salvar">ADICIONAR AO CARDÁPIO</button>

            </form>
        </div>
    </main>

    <nav class="navbar">
        <a href="indexRestaurante.php" class="desativo-hover"><img src="img/Icone Casa.png" class="img-nav" alt="Home/Dashboard"></a>

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

    /*============================================================== 
    FUNCIONALIDADE DE EXIBIR NOME DO ARQUIVO
    ==============================================================*/
    document.addEventListener('DOMContentLoaded', function() {
        const inputArquivo = document.getElementById('imagem_comida');
        const nomeArquivoSpan = document.getElementById('nome-arquivo-imagem');
        const textoPadrao = 'Foto do Prato';

        if (inputArquivo && nomeArquivoSpan) {
            inputArquivo.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    // Exibe o nome do arquivo, limitando o comprimento se necessário
                    nomeArquivoSpan.textContent = this.files[0].name;
                } else {
                    nomeArquivoSpan.textContent = textoPadrao;
                }
            });
        }
    });
</script>

</html>