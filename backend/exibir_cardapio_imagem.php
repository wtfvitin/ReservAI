<?php
// Tenta aumentar o limite de memória para garantir que BLOBs grandes sejam lidos
ini_set('memory_limit', '512M'); 
// Desliga o reporte de erros para evitar que mensagens de erro quebrem a imagem binária
error_reporting(0); 

// Inclui a conexão com o banco de dados
require_once "conexao.php";

// Define o caminho para a imagem placeholder (deve estar em ../img/placeholder.png)
$placeholder_path = '../img/placeholder.png'; 

/**
 * Função para servir uma imagem estática (placeholder).
 * Garante a limpeza do buffer de saída antes de enviar o cabeçalho.
 * @param string $caminho_arquivo O caminho do arquivo a ser servido.
 */
function servir_imagem_estatica($caminho_arquivo) {
    // Limpa o buffer de saída (CRUCIAL)
    if (ob_get_length()) {
        ob_clean();
    }
    
    if (file_exists($caminho_arquivo)) {
        $mime_type = 'image/png'; // Assume que o placeholder é PNG
        header("Content-Type: {$mime_type}"); 
        header("Content-Length: " . filesize($caminho_arquivo));
        header("Cache-Control: public, max-age=600"); 
        readfile($caminho_arquivo);
        exit;
    } else {
        // Se nem o placeholder for encontrado, retorna erro
        header("HTTP/1.0 500 Internal Server Error");
        die("Erro: Imagem placeholder não encontrada.");
    }
}


// Verifica se o ID do item do cardápio foi passado na URL
if (!isset($_GET['idcardapio'])) {
    // Se faltar o ID, serve o placeholder como fallback
    servir_imagem_estatica($placeholder_path);
}

$idcardapio = (int)$_GET['idcardapio'];

try {
    // Prepara a consulta para selecionar o BLOB da imagem específica na tabela 'cardapio'
    // O nome da coluna é 'foto_alimento'
    $sql = "SELECT foto_alimento FROM cardapio WHERE idcardapio = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $idcardapio, PDO::PARAM_INT);
    $stmt->execute();

    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    // 1. Verifica se o item do cardápio foi encontrado
    if (!$dados) {
        // Item não existe, serve o placeholder
        servir_imagem_estatica($placeholder_path);
    }
    
    // O conteúdo binário (BLOB) da imagem
    $imagem_blob = $dados['foto_alimento'];
    $tamanho_blob = strlen($imagem_blob); 

    // 2. Verifica se o campo BLOB está vazio ou nulo (tamanho zero)
    if (empty($imagem_blob) || $tamanho_blob === 0) {
        // Imagem não existe no banco, serve o placeholder
        servir_imagem_estatica($placeholder_path);
    }
    
    // =========================================================================
    // CORREÇÃO CRÍTICA: GARANTE QUE NÃO HÁ SAÍDA ANTERIOR E FORÇA O MIME TYPE
    // =========================================================================
    
    // Limpa o buffer de saída ANTES de enviar o cabeçalho (CRUCIAL para BLOBs)
    if (ob_get_length()) {
        ob_clean();
    }

    // Se a detecção de MIME (getimagesizefromstring) falhou, voltamos 
    // a assumir um tipo comum (JPEG), ou você pode tentar 'image/png'
    // Se suas imagens são mistas, tente 'image/jpeg' como padrão, 
    // mas se for PNG, troque para 'image/png'.
    $mime_type = 'image/jpeg'; 
    
    // Tentativa final de detecção para ser mais robusto, caso o getimagesizefromstring falhe
    // A função getimagesizefromstring está comentada, se for necessário.
    /*
    $image_info = @getimagesizefromstring($imagem_blob);
    if ($image_info !== false && isset($image_info['mime'])) {
        $mime_type = $image_info['mime'];
    }
    */
    
    // 3. ENVIA O CABEÇALHO CORRETO PARA A IMAGEM DO BANCO
    header("Content-Type: {$mime_type}"); 
    header("Content-Length: " . $tamanho_blob);
    header("Cache-Control: public, max-age=3600"); // Cache de 1 hora
    
    // 4. ENVIA O CONTEÚDO BINÁRIO
    echo $imagem_blob;

} catch (PDOException $e) {
    // Em caso de erro de banco de dados, serve o placeholder
    servir_imagem_estatica($placeholder_path);
} catch (Exception $e) {
    // Outros erros
    servir_imagem_estatica($placeholder_path);
}
?>