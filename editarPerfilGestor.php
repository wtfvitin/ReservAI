<?php
// Arquivo: editarPerfilRestaurante.php (V2)

// =========================================================
// 1. INICIA A SESSÃO, CONEXÃO E OBTÉM DADOS DO RESTAURANTE
// =========================================================
session_start();

// Caminho de conexão
require_once "backend/conexao.php";

// 🚨 Verifica se o ID do Restaurante está logado
if (empty($_SESSION['restaurante_id'])) {
    header("Location: loginRestaurante.html");
    exit;
}

$restaurante_id = $_SESSION['restaurante_id'];
$restaurante_data = [];
$mensagem_status = '';
$sucesso = false;

// 💡 SQL para buscar dados APENAS das colunas existentes na tabela 'restaurantes'
// O ID ainda precisa ser buscado apenas para garantir que o restaurante existe.
$sql_select = "SELECT 
    nome_restaurante, 
    telefone, 
    email_restaurante, 
    cep_res,
    endereco_rua_res, 
    endereco_num_res,
    endereco_bairro_res,
    endereco_cidade_res, 
    endereco_estado_res,
    logo_res
 FROM restaurantes 
 WHERE idrestaurante = :id";

try {
    $stmt = $pdo->prepare($sql_select);
    $stmt->execute([':id' => $restaurante_id]);
    $restaurante_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$restaurante_data) {
        session_destroy();
        header("Location: loginRestaurante.html?erro=restaurante_nao_encontrado");
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar dados do restaurante: " . $e->getMessage());
    die("Erro interno ao carregar perfil. Por favor, tente novamente.");
}

// 1.3. Preenche as variáveis com os dados atuais (sanitizados)
$nome = htmlspecialchars($restaurante_data['nome_restaurante']);
$email = htmlspecialchars($restaurante_data['email_restaurante']); // E-mail é buscado para exibição
$telefone = htmlspecialchars($restaurante_data['telefone']);

// Endereço
$cep = htmlspecialchars($restaurante_data['cep_res']);
$rua = htmlspecialchars($restaurante_data['endereco_rua_res']);
$numero = htmlspecialchars($restaurante_data['endereco_num_res']);
$bairro = htmlspecialchars($restaurante_data['endereco_bairro_res']);
$cidade = htmlspecialchars($restaurante_data['endereco_cidade_res']);
$estado = htmlspecialchars($restaurante_data['endereco_estado_res']);

// Foto: Usando 'logo_res' como foto de perfil/logo
$has_foto = !empty($restaurante_data['logo_res']);
$foto_base64 = $has_foto ? 'data:image/jpeg;base64,' . base64_encode($restaurante_data['logo_res']) : '';


// =========================================================
// 2. PROCESSAMENTO DO FORMULÁRIO (POST)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recebe e sanitiza dados
    $novo_nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    // O E-MAIL NÃO É MAIS RECEBIDO/VALIDADO VIA POST.
    $novo_telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_NUMBER_INT);

    $novo_cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_NUMBER_INT);
    $nova_rua = filter_input(INPUT_POST, 'rua', FILTER_SANITIZE_SPECIAL_CHARS);
    $novo_numero = filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_NUMBER_INT);
    $novo_bairro = filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_SPECIAL_CHARS);
    $nova_cidade = filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_SPECIAL_CHARS);
    $novo_estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_SPECIAL_CHARS);

    // Validação de campos obrigatórios (E-mail removido da validação)
    if (!$novo_nome || !$novo_telefone || !$novo_cep || !$nova_rua || !$novo_numero || !$novo_bairro || !$nova_cidade || !$novo_estado) {
        $mensagem_status = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        $campos_update = [];
        $params_update = [':id' => $restaurante_id];

        // 2.1. Monta os campos de texto para o UPDATE
        $campos_update[] = "nome_restaurante = :nome";
        $params_update[':nome'] = $novo_nome;

        // E-MAIL FOI REMOVIDO DO UPDATE.

        $campos_update[] = "telefone = :telefone";
        $params_update[':telefone'] = $novo_telefone;

        // Endereço
        $campos_update[] = "cep_res = :cep";
        $params_update[':cep'] = $novo_cep;
        $campos_update[] = "endereco_rua_res = :rua";
        $params_update[':rua'] = $nova_rua;
        $campos_update[] = "endereco_num_res = :numero";
        $params_update[':numero'] = $novo_numero;
        $campos_update[] = "endereco_bairro_res = :bairro";
        $params_update[':bairro'] = $novo_bairro;
        $campos_update[] = "endereco_cidade_res = :cidade";
        $params_update[':cidade'] = $nova_cidade;
        $campos_update[] = "endereco_estado_res = :estado";
        $params_update[':estado'] = $novo_estado;


        // =========================================================
        // 2.2. Lógica de Atualização/Remoção da Foto (Usando logo_res)
        // =========================================================
        $remover_foto = filter_input(INPUT_POST, 'remover_foto', FILTER_VALIDATE_INT);

        if ($remover_foto === 1 && $has_foto) {
            // Caso 1: O usuário clicou em "Remover Foto"
            $campos_update[] = "logo_res = NULL";
            $has_foto = false;
        } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            // Caso 2: O usuário enviou um novo arquivo
            $file_tmp = $_FILES['foto']['tmp_name'];

            // Verificação básica de tipo MIME
            $mime = mime_content_type($file_tmp);
            if (strpos($mime, 'image/') !== 0) {
                $mensagem_status = 'Erro: O arquivo enviado não é uma imagem.';
                goto fim_update;
            }

            $foto_binario = file_get_contents($file_tmp);

            $campos_update[] = "logo_res = :foto_perfil";
            $params_update[':foto_perfil'] = $foto_binario;
        }

        // Se não houver campos para atualizar, não faz o UPDATE
        if (empty($campos_update)) {
            $mensagem_status = 'Nenhuma alteração válida detectada.';
            goto fim_update;
        }

        // 2.3. Executa o UPDATE
        $sql_update = "UPDATE restaurantes SET " . implode(', ', $campos_update) . " WHERE idrestaurante = :id";

        try {
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute($params_update);

            $mensagem_status = 'Perfil atualizado com sucesso!';
            $sucesso = true;

            // Redireciona para o perfil após sucesso
            header("Location: perfilGestor.php?status=sucesso");
            exit;
        } catch (PDOException $e) {
            // Erro de duplicidade (só pode ser telefone, se houver UNIQUE) ou outro erro de DB
            error_log("Erro no UPDATE do restaurante: " . $e->getMessage());
            $mensagem_status = 'Erro interno ao atualizar perfil. Tente novamente.';

            // Se houver erro, preenche o formulário novamente para revisão
            $nome = $novo_nome;
            // $email é mantido pelo valor buscado no DB
            $telefone = $novo_telefone;
            $cep = $novo_cep;
            $rua = $nova_rua;
            $numero = $novo_numero;
            $bairro = $novo_bairro;
            $cidade = $nova_cidade;
            $estado = $novo_estado;
        }
    }
}

fim_update:
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/Logo.png">

    <link rel="stylesheet" href="src/css/padrão.css">
    <link rel="stylesheet" href="src/css/navbar.css">
    <link rel="stylesheet" href="src/css/editarPerfil.css">
    <title>Editar Perfil Restaurante - ReservAI</title>
</head>

<body>
    <div class="overlay" id="overlay"></div>
    <div class="search-container" id="searchBar">
        <form id="searchForm">
            <input type="search" id="searchInput" placeholder="Pesquisar...">
            <button type="submit"><img src="img/Icone Lupa.png" alt="Pesquisar"></button>
        </form>
    </div>

    <div class="barra-topo">

        <div class="esquerda">
            <img src="img/Icone Voltar.png" alt="Voltar" id="voltar" class="icone-voltar">
            <h1>Editar Perfil Restaurante</h1>
        </div>

    </div>

    <main class="perfil-container">

        <?php if (!empty($mensagem_status)) : ?>
            <div class="mensagem-status <?php echo $sucesso ? 'mensagem-sucesso' : 'mensagem-erro'; ?>">
                <?php echo $mensagem_status; ?>
            </div>
        <?php endif; ?>

        <form id="formEdicao" method="POST" enctype="multipart/form-data">

            <div class="perfil-header">
                <div class="profile-picture-wrapper">
                    <img id="profile_pic_preview"
                        src="<?php echo $has_foto ? $foto_base64 : 'https://placehold.co/120x120/d76a03/ffffff?text=' . substr($nome, 0, 1); ?>"
                        alt="Logo do Restaurante"
                        class="profile-picture">

                    <label for="foto_perfil_input" class="camera-icon">
                        <img src="img/Icone Camera.png" alt="Trocar Logo">
                    </label>
                    <input type="file" id="foto_perfil_input" name="foto" accept="image/*" onchange="previewImage(event)">
                </div>

                <input type="hidden" id="remover_foto_input" name="remover_foto" value="0">
                <br>
                <?php if ($has_foto) : ?>
                    <button type="button" class="btn-remover-foto" id="btnRemoverFoto">Remover Logo</button>
                <?php endif; ?>

            </div>

            <section class="info-section">
                <h3>Informações do Restaurante</h3>

                <div class="info-card">

                    <div class="form-group">
                        <label for="nome">Nome do Restaurante</label>
                        <input type="text" id="nome" name="nome" value="<?php echo $nome; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="<?php echo $email; ?>" required readonly>
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" value="<?php echo $telefone; ?>" required>
                    </div>

                </div>
            </section>

            <section class="info-section">
                <h3>Endereço</h3>

                <div class="info-card">
                    <div class="form-group">
                        <label for="cep">CEP</label>
                        <input type="text" id="cep" name="cep" value="<?php echo $cep; ?>" required>
                    </div>

                    <div class="inline-group">
                        <div class="form-group flex-2">
                            <label for="rua">Rua</label>
                            <input type="text" id="rua" name="rua" value="<?php echo $rua; ?>" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="numero">Número</label>
                            <input type="text" id="numero" name="numero" value="<?php echo $numero; ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bairro">Bairro</label>
                        <input type="text" id="bairro" name="bairro" value="<?php echo $bairro; ?>" required>
                    </div>

                    <div class="inline-group">
                        <div class="form-group">
                            <label for="cidade">Cidade</label>
                            <input type="text" id="cidade" name="cidade" value="<?php echo $cidade; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado (UF)</label>
                            <input type="text" id="estado" name="estado" maxlength="2" value="<?php echo $estado; ?>" required>
                        </div>
                    </div>
                </div>
            </section>

            <button type="submit" class="btn-salvar">Salvar Alterações</button>

        </form>

    </main>

    <br><br><br>
    <br><br><br>


    <div class="overlay" id="overlay"></div>

    <nav class="navbar">
        <a href="indexRestaurante.php" class="desativo-hover">
            <img src="img/Icone Casa.png" class="img-nav" alt="Home">
        </a>

        <a href="configuracoesRestaurante.html" class="desativo-hover">
            <img src="img/Icone Configurações.png" class="img-nav" alt="Configurações">
        </a>

        <a href="perfilGestor.php" class="ativo-hover">
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
    PREVIEW DE IMAGEM
    ==============================================================*/
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('profile_pic_preview');
                output.src = reader.result;
            }
            reader.readAsDataURL(file);
        }
    }

    /*============================================================== 
    INTEGRAÇÃO VIACEP
    ==============================================================*/
    const cepInput = document.getElementById('cep');
    const ruaInput = document.getElementById('rua');
    const numeroInput = document.getElementById('numero'); // Novo campo
    const bairroInput = document.getElementById('bairro'); // Novo campo
    const cidadeInput = document.getElementById('cidade');
    const estadoInput = document.getElementById('estado');

    function limparFormularioEndereco() {
        ruaInput.value = "";
        numeroInput.value = "";
        bairroInput.value = "";
        cidadeInput.value = "";
        estadoInput.value = "";
    }

    async function buscarCEP() {
        // Remove caracteres não numéricos
        const cep = cepInput.value.replace(/\D/g, '');

        if (cep.length != 8) {
            return;
        }

        // Exibe um breve indicador de carregamento
        ruaInput.value = "Buscando...";
        bairroInput.value = "Buscando...";
        cidadeInput.value = "Buscando...";
        estadoInput.value = "Buscando...";

        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();

            if (!data.erro) {
                ruaInput.value = data.logradouro || '';
                bairroInput.value = data.bairro || '';
                cidadeInput.value = data.localidade || '';
                estadoInput.value = data.uf || '';

                // Move o foco para o número, que é o próximo campo de preenchimento manual
                if (ruaInput.value.trim() !== "") {
                    numeroInput.focus();
                } else {
                    ruaInput.focus();
                }
            } else {
                limparFormularioEndereco();
                alert("CEP não encontrado.");
                cepInput.focus();
            }
        } catch (error) {
            limparFormularioEndereco();
            console.error("Erro na consulta ViaCEP:", error);
            alert("Erro ao conectar com o serviço de CEP.");
        }
    }

    // Adiciona o listener para buscar CEP quando o campo perde o foco
    cepInput.addEventListener('blur', buscarCEP);


    /*============================================================== 
      SUBMISSÃO GERAL DO FORMULÁRIO (Função de idade removida)
      ==============================================================*/
    const formEdicao = document.getElementById('formEdicao');

    // Nenhuma validação de idade necessária para perfil de restaurante.
    // A submissão continua normalmente.


    /*============================================================== 
        BOTÃO REMOVER FOTO
        ==============================================================*/
    const btnRemoverFoto = document.getElementById('btnRemoverFoto');
    const removerFotoInput = document.getElementById('remover_foto_input');

    if (btnRemoverFoto) {
        btnRemoverFoto.addEventListener('click', function(e) {
            e.preventDefault();

            if (confirm('Tem certeza que deseja remover o logo do restaurante?')) {
                // 1. Define o campo oculto para 1 (Sinaliza a remoção no PHP)
                removerFotoInput.value = '1';

                // Limpa o input de arquivo para evitar que uma imagem acidentalmente selecionada seja enviada
                const fotoInput = document.getElementById('foto_perfil_input');
                if (fotoInput) fotoInput.value = '';

                // 2. Submete o formulário *imediatamente*.
                formEdicao.submit();
            }
        });
    }
</script>

</html>