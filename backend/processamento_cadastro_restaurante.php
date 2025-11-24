<?php
// Inclui a conexão e inicia a sessão
require_once "conexao.php";
session_start();

/**
 * Limpa todas as variáveis de sessão temporárias usadas para o cadastro,
 * MANTENDO a variável de ID necessária para a próxima etapa.
 */
function limparVariaveisCadastro() {
    $keys_to_unset = [
        "cadastroRestaurante_email",
        "cadastroRestaurante_senha",
        "cadastroRestaurante_nome_restaurante",
        "cadastroRestaurante_telefone",
        "cadastroRestaurante_horarioA",
        "cadastroRestaurante_horarioF",
        "cadastroRestaurante_cep",
        "cadastroRestaurante_endereco",
        "cadastroRestaurante_numero",
        "cadastroRestaurante_bairro",
        "cadastroRestaurante_cidade",
        "cadastroRestaurante_estado",
    ];

    foreach ($keys_to_unset as $key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
}


// Mapeamento das chaves do campo de upload para as colunas BLOB do banco de dados
$mapa_uploads_db = [
    'logo_upload' => 'logo_res',
    'fotoPrincipal_upload' => 'fotoPrincipal_res',
    'foto1_upload' => 'foto1_res',
    'foto2_upload' => 'foto2_res',
    'foto3_upload' => 'foto3_res',
];

// =========================================================================
// FLUXO DE INSERÇÃO ÚNICA (Todos os dados, incluindo BLOBs, em uma só query)
// =========================================================================

// Verifica se a requisição é um POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ---------------------------------------------------------------------
    // 1. Recuperação e Validação dos Dados de Texto
    // ---------------------------------------------------------------------

    // Os dados são puxados da SESSION, pois vieram de passos anteriores
    $email               = $_SESSION["cadastroRestaurante_email"]          ?? null;
    $senha               = $_SESSION["cadastroRestaurante_senha"]          ?? null;
    $nomeRestaurante     = $_SESSION["cadastroRestaurante_nome_restaurante"] ?? null;
    $telefone            = $_SESSION["cadastroRestaurante_telefone"]         ?? null;
    $horario_a           = $_SESSION["cadastroRestaurante_horarioA"]         ?? null;
    $horario_f           = $_SESSION["cadastroRestaurante_horarioF"]         ?? null;

    // Dados de Endereço
    $cep       = trim($_SESSION["cadastroRestaurante_cep"] ?? '');
    $endereco  = trim($_SESSION["cadastroRestaurante_endereco"] ?? '');
    $numero    = trim($_SESSION["cadastroRestaurante_numero"] ?? '');
    $bairro    = trim($_SESSION["cadastroRestaurante_bairro"] ?? '');
    $cidade    = trim($_SESSION["cadastroRestaurante_cidade"] ?? '');
    $estado    = trim($_SESSION["cadastroRestaurante_estado"] ?? '');
    
    // Dados de descrição (vindo do POST do formulário final)
    $descricao = $_POST['descricao'] ?? null;
    
    // Verificação de segurança: checa se os dados obrigatórios estão presentes
    if (!$email || !$senha || !$nomeRestaurante || !$telefone || !$horario_a || !$horario_f || !$cep || !$endereco || !$numero || !$bairro || !$cidade || !$estado || !$descricao) {
        die("Erro: dados de texto incompletos. Por favor, volte e preencha todos os campos.");
    }
    
    // Verifica se o email já está cadastrado
    try {
        $check = $pdo->prepare("SELECT idrestaurante FROM restaurantes WHERE email_restaurante = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            echo "E-mail já cadastrado!";
            exit;
        }
    } catch (PDOException $e) {
        die("Erro de Banco de Dados na Verificação de E-mail: " . $e->getMessage());
    }

    // ---------------------------------------------------------------------
    // 2. Processamento dos Uploads de Arquivo (BLOBs)
    // ---------------------------------------------------------------------
    $parametros_blob = [];
    $upload_ok = true;

    foreach ($mapa_uploads_db as $nome_campo_form => $coluna_db) {
        // Assume NULL se o arquivo não foi enviado ou houve erro
        $conteudo_binario = null; 
        
        if (isset($_FILES[$nome_campo_form]) && $_FILES[$nome_campo_form]['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES[$nome_campo_form];

            // Leitura do conteúdo binário do arquivo temporário
            $conteudo_binario = file_get_contents($arquivo['tmp_name']);

            if ($conteudo_binario === false) {
                $upload_ok = false;
                die("Erro ao ler o arquivo binário do campo: {$nome_campo_form}.");
            }
        }
        
        // Adiciona o conteúdo binário (ou null) ao array de parâmetros BLOB
        $parametros_blob[$coluna_db] = $conteudo_binario;
    }

    if (!$upload_ok) {
        die("Erro: Ocorreu um problema em um dos uploads de arquivo.");
    }

    // ---------------------------------------------------------------------
    // 3. Montagem e Execução da Query de INSERT Único
    // ---------------------------------------------------------------------
    try {
        // Criptografa a senha antes de salvar
        $senhaHash = $senha;
        
        // Monta os nomes das colunas e os placeholders
        $colunas = [
            'nome_restaurante', 'telefone', 'email_restaurante', 'horario_abertura', 'horario_fechamento', 
            'cep_res', 'endereco_rua_res', 'endereco_num_res', 'endereco_bairro_res', 'endereco_cidade_res', 
            'endereco_estado_res', 'senha', 'descricao'
        ];
        $placeholders = [
            '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?'
        ];

        // Adiciona as colunas e placeholders dos BLOBs
        foreach (array_keys($parametros_blob) as $coluna_blob) {
            $colunas[] = $coluna_blob;
            $placeholders[] = '?';
        }

        // Query de inserção
        $sql = "INSERT INTO restaurantes (" . implode(', ', $colunas) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $pdo->prepare($sql);
        
        // 4. Monta o array de valores
        $valores = [
            $nomeRestaurante, $telefone, $email, $horario_a, $horario_f, $cep, $endereco, 
            $numero, $bairro, $cidade, $estado, $senhaHash, $descricao
        ];

        // Adiciona os valores dos BLOBs (o conteúdo binário ou null)
        $valores = array_merge($valores, array_values($parametros_blob));
        
        // 5. Configuração para enviar os dados como BLOBs
        $param_index = 1;
        
        // Bind dos campos de texto (1 a 13)
        for ($i = 0; $i < 13; $i++) {
            $stmt->bindParam($param_index++, $valores[$i]);
        }

        // Bind dos campos BLOBs (14 em diante)
        for ($i = 13; $i < count($valores); $i++) {
            // É crucial usar PARAM_LOB para binários grandes
            $stmt->bindParam($param_index++, $valores[$i], PDO::PARAM_LOB); 
        }
        
        // 6. Executa a query
        $ok = $stmt->execute();

        // 7. Resposta e Redirecionamento
        if ($ok) {
            // Obtém o ID do restaurante recém-criado
            $restaurante_id = $pdo->lastInsertId();

            // *** CORREÇÃO: Salva o ID na sessão para a próxima etapa (Cardápio)
            $_SESSION['restaurante_id'] = $restaurante_id;
            
            // Limpa as variáveis de cadastro temporárias, mas MANTÉM a sessão ativa
            limparVariaveisCadastro();
            
            // Redireciona para o próximo passo (Cardápio)
            header("Location: ../personalizarRestaurantePt2.php?sucesso=1");
            exit;
        } else {
            echo "Erro ao cadastrar. Tente novamente.";
        }

    } catch (PDOException $e) {
        // Em caso de falha
        die("Erro de Banco de Dados na Inserção Completa: " . $e->getMessage());
    }

} else {
    // Redireciona se não for um POST
    header("Location: ../cadastroRestaurantePt1.html");
    exit;
}
?>