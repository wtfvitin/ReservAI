<?php
// Inclui a conexão com o banco de dados
require_once "conexao.php";

// Define o caminho para a imagem placeholder
$placeholder_path = '../img/placeholder.png'; // Caminho relativo a 'backend/'

// Função para servir uma imagem estática
function servir_imagem_estatica($caminho_arquivo) {
    if (file_exists($caminho_arquivo)) {
        $mime_type = 'image/png'; // O placeholder é PNG
        header("Content-Type: {$mime_type}"); 
        header("Content-Length: " . filesize($caminho_arquivo));
        // Impede o cache se for o placeholder, ou coloca um cache curto
        header("Cache-Control: public, max-age=600"); 
        readfile($caminho_arquivo);
        exit;
    } else {
        // Se nem o placeholder for encontrado, retorna erro
        header("HTTP/1.0 500 Internal Server Error");
        die("Erro: Imagem placeholder não encontrada em {$caminho_arquivo}.");
    }
}


// Verifica se os parâmetros necessários (id e tipo) foram passados na URL
if (!isset($_GET['id']) || !isset($_GET['tipo'])) {
    // Se faltarem parâmetros, serve o placeholder como fallback
    servir_imagem_estatica($placeholder_path);
}

$restaurante_id = (int)$_GET['id'];
$tipo_imagem = $_GET['tipo'];

// Mapeamento dos tipos de imagem URL para os nomes das colunas no banco de dados
$colunas_validas = [
    'logo' => 'logo_res',
    'fotoPrincipal' => 'fotoPrincipal_res',
    'foto1' => 'foto1_res',
    'foto2' => 'foto2_res',
    'foto3' => 'foto3_res',
];

// Valida o 'tipo' de imagem solicitado
if (!isset($colunas_validas[$tipo_imagem])) {
    // Se o tipo for inválido, serve o placeholder como fallback
    servir_imagem_estatica($placeholder_path);
}

$coluna_db = $colunas_validas[$tipo_imagem];

try {
    // Prepara a consulta para selecionar o BLOB da imagem específica
    $sql = "SELECT {$coluna_db} FROM restaurantes WHERE idrestaurante = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $restaurante_id, PDO::PARAM_INT);
    $stmt->execute();

    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    // 1. Verifica se o restaurante foi encontrado
    if (!$dados) {
        // Restaurante não existe, serve o placeholder
        servir_imagem_estatica($placeholder_path);
    }
    
    // O conteúdo binário (BLOB) da imagem
    $imagem_blob = $dados[$coluna_db];

    // 2. Verifica se o campo BLOB está vazio ou nulo
    if (empty($imagem_blob)) {
        // Imagem não existe no banco, serve o placeholder
        servir_imagem_estatica($placeholder_path);
    }

    // 3. ENVIA O CABEÇALHO CORRETO PARA A IMAGEM DO BANCO
    // Nota: Se você tem certeza do tipo de imagem (ex: só PNG ou só JPEG), use o Content-Type exato.
    // Como estamos usando BLOB, vamos assumir JPEG, mas ajuste se necessário.
    header("Content-Type: image/jpeg"); 
    header("Content-Length: " . strlen($imagem_blob));
    header("Cache-Control: public, max-age=3600"); // Cache de 1 hora
    
    // 4. ENVIA O CONTEÚDO BINÁRIO
    echo $imagem_blob;

} catch (PDOException $e) {
    // Em caso de erro de banco de dados, serve o placeholder
    error_log("Erro de PDO ao buscar imagem: " . $e->getMessage());
    servir_imagem_estatica($placeholder_path);
}
?>