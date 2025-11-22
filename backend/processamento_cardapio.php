<?php
// Certifique-se de iniciar a sessão para obter o ID do restaurante
session_start();

// --- 1. INCLUSÃO DA CONEXÃO ---
// O arquivo de conexão deve estar no mesmo diretório (backend/).
require_once 'conexao.php';

// Verifica se a variável $pdo de conexão foi definida
if (!isset($pdo)) {
    die("Erro: A variável \$pdo de conexão não foi definida por conexao.php.");
}

// --- 2. VERIFICAÇÃO E OBTENÇÃO DE DADOS ---
// O ID do restaurante deve estar na sessão, POIS FOI SALVO NO PASSO ANTERIOR.
if (!isset($_SESSION['restaurante_id'])) {
    // Erro que indica falha no passo anterior
    header("Location: ../personalizarRestaurantePt2.php?status=erro_sessao");
    exit();
}

$restaurante_id = $_SESSION['restaurante_id'];
$total_pratos = (int)($_POST['total_pratos'] ?? 0);

if ($total_pratos < 1 || $total_pratos > 20) {
    // Redireciona se o número de pratos for inválido
    header("Location: ../personalizarRestaurantePt2.php?status=erro_num_pratos");
    exit();
}

// Prepara a query de inserção para a tabela 'cardapio'
$stmt_insert = $pdo->prepare("INSERT INTO cardapio (restaurante_id, nome_alimento, foto_alimento, preco) VALUES (?, ?, ?, ?)");

$pratos_inseridos = 0;

// --- 3. PROCESSAMENTO EM LOOP DOS DADOS DINÂMICOS ---

for ($i = 1; $i <= $total_pratos; $i++) {
    // Nomes dos campos esperados no POST/FILES
    $nome_campo_imagem = "imagem_comida_{$i}";
    $nome_campo_nome = "nome_prato_{$i}";
    $nome_campo_preco = "preco_comida_{$i}";

    // Lógica para nome do prato (mantida para robustez)
    $nome_prato = "Comida {$i}";
    if (isset($_POST[$nome_campo_nome])) {
        // Usa o nome real do prato (campo que você adicionou)
        $nome_prato = trim($_POST[$nome_campo_nome]);
    }

    // Inicializa o preço e o conteúdo da imagem para o bind
    $preco = 0.00;
    $conteudo_imagem = null;
    $prato_valido = false;

    // Verifica se os dados necessários existem para este prato
    if (
        isset($_FILES[$nome_campo_imagem]) &&
        isset($_POST[$nome_campo_preco]) &&
        !empty($nome_prato) // Garante que o nome do prato não está vazio
    ) {
        // Limpa o preço, substituindo vírgula por ponto para o formato DECIMAL do MySQL
        $preco_str = str_replace(',', '.', $_POST[$nome_campo_preco]);
        $preco = (float) $preco_str;

        // --- Tratamento da Imagem (Upload) ---
        $upload_file = $_FILES[$nome_campo_imagem];

        if ($upload_file['error'] === UPLOAD_ERR_OK) {
            // Lê o conteúdo da imagem (BLOB)
            $conteudo_imagem = file_get_contents($upload_file['tmp_name']);

            if ($conteudo_imagem !== false) {
                $prato_valido = true;
            } else {
                error_log("Erro ao ler conteúdo binário para o prato {$i}.");
            }
        } else {
            error_log("Erro no upload do arquivo para o prato {$i}. Código: " . $upload_file['error']);
        }
    } else {
        error_log("Dados incompletos (imagem, nome ou preço) para o prato {$i}.");
    }

    // Insere os dados no banco de dados, se o prato for válido
    if ($prato_valido) {
        try {
            // --- RECOMENDADO: Bind manual para garantir o tratamento do BLOB ---
            $stmt_insert->bindParam(1, $restaurante_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(2, $nome_prato, PDO::PARAM_STR);
            $stmt_insert->bindParam(3, $conteudo_imagem, PDO::PARAM_LOB); // USO OBRIGATÓRIO PARA BLOB
            $stmt_insert->bindParam(4, $preco);

            $stmt_insert->execute();
            $pratos_inseridos++;
        } catch (\PDOException $e) {
            // Em caso de erro, registra e tenta o próximo prato
            error_log("Erro ao inserir prato {$i} no banco: " . $e->getMessage());
        }
    }
}

// --- 4. REDIRECIONAMENTO FINAL E LIMPEZA DA SESSÃO ---

if ($pratos_inseridos > 0) {
    // O cadastro foi concluído com sucesso.

    // Destrói TODAS as variáveis de sessão, finalizando o fluxo de cadastro.
    session_unset();
    session_destroy();

    // Redireciona para a página final do restaurante (ex: dashboard ou login)
    header("Location: ../indexRestaurante.php?cadastro=sucesso");
    exit();
} else {
    // Se nenhum prato foi inserido, redireciona de volta para a etapa 2.
    header("Location: ../personalizarRestaurantePt2.php?status=erro_sem_pratos");
    exit();
}
// O script termina aqui.
