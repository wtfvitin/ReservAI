<?php
// =================================================================
// CÓDIGO PHP PARA TRATAMENTO E ATUALIZAÇÃO DO PERFIL
// =================================================================

// Simulação de variáveis de sessão para o usuário logado
// Em um sistema real, você verificaria se o usuário está logado aqui.
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redireciona para a página de login se não estiver logado
    // header('Location: login.php');
    // exit;
}

// Conexão com o banco de dados (Exemplo - substitua pelas suas credenciais)
// require_once 'db_config.php';
$db = new stdClass(); // Simulação de objeto de banco de dados

// Dados do usuário (Geralmente carregados do banco de dados)
$userId = $_SESSION['user_id'] ?? 1; // Usando 1 para simulação
$nome = "Nome do Usuário";
$email = "usuario@exemplo.com";
$biografia = "Esta é a biografia atual do usuário, que será editada.";

$mensagem_status = "";

// 1. Verificação de Submissão do Formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Coleta e Sanitização dos Dados
    $novo_nome = htmlspecialchars(trim($_POST['nome']));
    $novo_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $nova_biografia = htmlspecialchars(trim($_POST['biografia']));

    // 3. Validação Básica
    if (empty($novo_nome) || empty($novo_email)) {
        $mensagem_status = "<p class='text-red-500 font-bold'>Erro: Nome e E-mail são obrigatórios.</p>";
    } elseif (!filter_var($novo_email, FILTER_VALIDATE_EMAIL)) {
        $mensagem_status = "<p class='text-red-500 font-bold'>Erro: E-mail inválido.</p>";
    } else {
        // 4. Lógica de Atualização do Banco de Dados
        // =================================================================
        /*
        // Exemplo de atualização no MySQLi/PDO:
        $stmt = $db->prepare("UPDATE usuarios SET nome = ?, email = ?, biografia = ? WHERE id = ?");
        $stmt->bind_param("sssi", $novo_nome, $novo_email, $nova_biografia, $userId);
        if ($stmt->execute()) {
            $mensagem_status = "<p class='text-green-500 font-bold'>Perfil atualizado com sucesso!</p>";
            // Atualiza as variáveis locais para refletir a mudança no formulário
            $nome = $novo_nome;
            $email = $novo_email;
            $biografia = $nova_biografia;
        } else {
            $mensagem_status = "<p class='text-red-500 font-bold'>Erro ao atualizar: " . $db->error . "</p>";
        }
        $stmt->close();
        */
        // Simulação de sucesso:
        $mensagem_status = "<p class='text-green-500 font-bold'>Perfil atualizado com sucesso (Simulado)!</p>";
        $nome = $novo_nome;
        $email = $novo_email;
        $biografia = $nova_biografia;
        // =================================================================
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <!-- Incluindo Tailwind CSS para estilização (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Define a fonte Inter como padrão */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f7f7;
        }
        /* Estilos customizados para o formulário */
        .form-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        .form-input, .form-textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: border-color 0.2s;
        }
        .form-input:focus, .form-textarea:focus {
            border-color: #4f46e5; /* Indigo-600 */
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
    </style>
</head>
<body>

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white shadow-xl rounded-xl p-8 sm:p-10">

            <!-- Título e Mensagem de Status -->
            <h1 class="text-3xl font-extrabold text-gray-900 mb-6 text-center">
                Editar Meu Perfil
            </h1>
            <p class="text-gray-500 text-center mb-8">
                Atualize suas informações abaixo.
            </p>

            <div class="text-center mb-6">
                <?php echo $mensagem_status; ?>
            </div>

            <!-- Formulário de Edição -->
            <!-- Note: O action vazio envia para a própria página -->
            <form action="editarPerfil.php" method="POST" enctype="multipart/form-data">

                <!-- Campo de Nome -->
                <div class="mb-4">
                    <label for="nome" class="form-label">Nome Completo</label>
                    <!-- O campo de input carrega o valor atual do PHP -->
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required 
                           class="form-input transition duration-150 ease-in-out">
                </div>

                <!-- Campo de E-mail -->
                <div class="mb-4">
                    <label for="email" class="form-label">Endereço de E-mail</label>
                    <!-- O campo de input carrega o valor atual do PHP -->
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required 
                           class="form-input transition duration-150 ease-in-out">
                </div>

                <!-- Campo de Biografia/Sobre Mim -->
                <div class="mb-4">
                    <label for="biografia" class="form-label">Biografia / Sobre Mim</label>
                    <!-- O campo de textarea carrega o valor atual do PHP -->
                    <textarea id="biografia" name="biografia" rows="4" 
                              class="form-textarea transition duration-150 ease-in-out"><?php echo htmlspecialchars($biografia); ?></textarea>
                </div>

                <!-- Campo de Upload de Foto (Exemplo) -->
                <div class="mb-6">
                    <label for="foto_perfil" class="form-label">Alterar Foto de Perfil</label>
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" 
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition duration-150 ease-in-out">
                    <p class="text-xs text-gray-500 mt-1">Envie um arquivo JPG, PNG ou GIF.</p>
                </div>
                
                <!-- Botão de Salvar Alterações -->
                <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg 
                               shadow-md hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-[1.01]">
                    Salvar Alterações
                </button>

            </form>

            <!-- Link de volta para a página de perfil (visualização) -->
            <div class="mt-6 text-center">
                <a href="perfil.php" class="text-indigo-600 hover:text-indigo-800 font-medium transition duration-150 ease-in-out">
                    ← Voltar para o Meu Perfil
                </a>
            </div>

        </div>
    </div>

</body>
</html>