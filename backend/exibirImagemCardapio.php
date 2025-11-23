<?php
// Arquivo: backend/exibirImagemCardapio.php
// Objetivo: Carregar e exibir a imagem BLOB de um item do cardápio.

// --- 1. INCLUSÃO DA CONEXÃO E VERIFICAÇÃO DE ID ---
require_once 'conexao.php'; // Certifique-se de que o caminho para sua conexão está correto

// Verifica se o ID do item foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(404);
    exit();
}

$id_cardapio = (int)$_GET['id'];

// --- 2. BUSCA DA IMAGEM BLOB ---
try {
    // Busca a foto_alimento (BLOB)
    $sql = "SELECT foto_alimento FROM cardapio WHERE idcardapio = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_cardapio]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item && !empty($item['foto_alimento'])) {
        $imagem_blob = $item['foto_alimento'];

        // --- 3. SAÍDA DA IMAGEM ---
        
        // Define o cabeçalho de conteúdo como imagem (Assumindo que você usa JPEG/PNG/WebP)
        // Se a imagem não for reconhecida, o navegador tentará adivinhar
        header("Content-Type: image/jpeg"); // Tipo de conteúdo genérico para imagens
        
        // Define cabeçalhos para evitar cache
        header("Cache-Control: public, max-age=3600"); 
        header("Expires: " . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        
        // Envia os dados binários da imagem
        echo $imagem_blob;
        
    } else {
        // Se a imagem não for encontrada (opcional: redirecionar para um placeholder genérico)
        http_response_code(404);
        echo "Imagem não encontrada.";
    }
    
} catch (\PDOException $e) {
    // Em caso de erro do BD
    error_log("Erro ao carregar imagem BLOB: " . $e->getMessage());
    http_response_code(500);
}
exit();