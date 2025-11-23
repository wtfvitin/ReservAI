<?php
// Arquivo: gerenciarCardapio.php

session_start();
include_once 'backend/conexao.php'; // Inclui a conexão PDO

// VERIFICAÇÃO DE LOGIN
if (!isset($_SESSION['restaurante_id'])) {
    // Redireciona para o login se não estiver logado
    header("Location: loginRestaurante.html");
    exit;
}

$restaurante_id = $_SESSION['restaurante_id'];
$itens_cardapio = [];
$mensagem = '';

// Verifica se há mensagens de status na URL (ex: após uma exclusão ou adição)
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'excluido') {
        $mensagem = "Item excluído com sucesso!";
    } elseif ($_GET['status'] == 'adicionado') {
        $mensagem = "Novo item adicionado com sucesso!";
    }
}

// 1. PROCESSAR EXCLUSÃO (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_id'])) {
    $id_para_excluir = $_POST['excluir_id'];

    try {
        // Garante que o item pertence ao restaurante logado antes de excluir
        $sql_delete = "DELETE FROM cardapio WHERE idcardapio = ? AND restaurante_id = ?";
        $stmt_delete = $pdo->prepare($sql_delete);

        if ($stmt_delete->execute([$id_para_excluir, $restaurante_id])) {
            // Redireciona para o GET para limpar o POST e mostrar a mensagem
            header("Location: gerenciarCardapio.php?status=excluido");
            exit;
        } else {
            $mensagem = "Erro ao excluir o item do cardápio.";
        }
    } catch (PDOException $e) {
        $mensagem = "Erro de exclusão: " . $e->getMessage();
    }
}

// 2. BUSCAR ITENS ATUAIS DO CARDÁPIO (Sempre executado, após exclusão ou não)
try {
    // Busca todos os campos necessários da tabela 'cardapio'
    $sql = "SELECT idcardapio, nome_alimento, preco, foto_alimento 
      FROM cardapio 
      WHERE restaurante_id = ? 
      ORDER BY nome_alimento ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$restaurante_id]);
    $itens_cardapio = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem = "Erro ao carregar cardápio: " . $e->getMessage();
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
    <link rel="stylesheet" href="src/css/gerenciarCardapio.css">
    <title>Gerenciar Cardápio - ReservAI</title>
</head>

<body>
    <div class="barra-topo">
        <div class="esquerda">
            <img src="img/Icone Voltar.png" alt="Voltar" id="voltar" class="icone-voltar">
            <h1>Gerenciar Cardápio</h1>
        </div>
    </div>

    <main>
        <div class="cardapio-header">
            <h2>Itens Atuais</h2>
            <a href="adicionarItemCardapio.php" class="btn-primary btn-adicionar">
                + Adicionar Item
            </a>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem sucesso" style="max-width: 800px; margin: 10px auto;">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <section class="cardapio-list">
            <?php if (count($itens_cardapio) > 0): ?>
                <?php foreach ($itens_cardapio as $item): ?>
                    <div class="item-card">

                        <div class="item-card-img-container">
                            <img src="backend/exibirImagemCardapio.php?id=<?php echo $item['idcardapio']; ?>"
                                alt="<?php echo htmlspecialchars($item['nome_alimento']); ?>"
                                class="item-card-img">
                        </div>

                        <div class="item-card-info">
                            <h3><?php echo htmlspecialchars($item['nome_alimento']); ?></h3>
                        </div>

                        <div class="item-card-preco">
                            R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?>
                        </div>

                        <div class="item-card-actions">

                            <form method="POST" action="gerenciarCardapio.php" style="display: inline-block; background: transparent;" onsubmit="return confirm('Tem certeza que deseja excluir o item &quot;<?php echo htmlspecialchars($item['nome_alimento']); ?>&quot;?');">
                                <input type="hidden" name="excluir_id" value="<?php echo $item['idcardapio']; ?>">
                                <button type="submit" title="Excluir" class="excluir">
                                    <img src="img/Icone Excluir.png" alt="Excluir">
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-items">
                    O cardápio está vazio. Clique em "+ Adicionar Item" para começar.
                </div>
            <?php endif; ?>
        </section>

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
</script>

</html>