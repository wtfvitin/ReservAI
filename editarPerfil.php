<?php
// =========================================================
// 1. INICIA A SESSÃO, CONEXÃO E OBTÉM DADOS DO CLIENTE
// =========================================================
session_start();
// O arquivo "backend/conexao.php" é necessário para a conexão PDO.
require_once "backend/conexao.php";

if (empty($_SESSION['usuario_id'])) {
    // Redireciona se o usuário não estiver logado
    header("Location: cadastroClientePt1.php");
    exit;
}

$cliente_id = $_SESSION['usuario_id'];
$cliente_data = [];
$mensagem_status = '';
$sucesso = false;

// SQL para buscar todos os dados do cliente, incluindo a foto
$sql_select = "SELECT 
   nome_cli, 
   sobrenome_cli, 
   email_cli, 
   telefone_cli,
   dtNasc_cli,
   cpf_cli,
   cep_cli,
   endereco_rua_cli, 
   endereco_cidade_cli, 
   endereco_estado_cli,
   foto_perfil
  FROM clientes 
  WHERE idcliente = :id";

try {
    $stmt = $pdo->prepare($sql_select);
    $stmt->execute([':id' => $cliente_id]);
    $cliente_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente_data) {
        session_destroy();
        header("Location: cadastroClientePt1.php?erro=usuario_nao_encontrado");
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar dados do cliente: " . $e->getMessage());
    die("Erro interno ao carregar perfil. Por favor, tente novamente.");
}

// 1.3. Preenche as variáveis com os dados atuais (sanitizados)
$nome_cli = htmlspecialchars($cliente_data['nome_cli']);
$sobrenome_cli = htmlspecialchars($cliente_data['sobrenome_cli']);
$email_cli = htmlspecialchars($cliente_data['email_cli']);
$telefone_cli = htmlspecialchars($cliente_data['telefone_cli']);
$dtNasc_cli = htmlspecialchars($cliente_data['dtNasc_cli']);
$cpf_cli = htmlspecialchars($cliente_data['cpf_cli']);
$cep_cli = htmlspecialchars($cliente_data['cep_cli']);
$rua_cli = htmlspecialchars($cliente_data['endereco_rua_cli']);
$cidade_cli = htmlspecialchars($cliente_data['endereco_cidade_cli']);
$estado_cli = htmlspecialchars($cliente_data['endereco_estado_cli']);

$has_foto = !empty($cliente_data['foto_perfil']);
$foto_base64 = $has_foto ? 'data:image/jpeg;base64,' . base64_encode($cliente_data['foto_perfil']) : '';


// =========================================================
// 2. PROCESSAMENTO DO FORMULÁRIO (POST)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recebe e sanitiza dados
    $novo_nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $novo_sobrenome = filter_input(INPUT_POST, 'sobrenome', FILTER_SANITIZE_SPECIAL_CHARS);
    $novo_email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL); // Valida e sanitiza
    $novo_telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_NUMBER_INT);
    $novo_dtNasc = filter_input(INPUT_POST, 'dataNascimento');
    $novo_cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_NUMBER_INT);
    $nova_rua = filter_input(INPUT_POST, 'rua', FILTER_SANITIZE_SPECIAL_CHARS);
    $nova_cidade = filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_SPECIAL_CHARS);
    $novo_estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_SPECIAL_CHARS);

    // Validação de campos obrigatórios
    if (!$novo_nome || !$novo_email || !$novo_telefone || !$novo_dtNasc || !$novo_cep || !$nova_rua || !$nova_cidade || !$novo_estado) {
        $mensagem_status = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        $campos_update = [];
        $params_update = [':id' => $cliente_id];

        // 2.1. Monta os campos de texto para o UPDATE
        $campos_update[] = "nome_cli = :nome";
        $params_update[':nome'] = $novo_nome;
        $campos_update[] = "sobrenome_cli = :sobrenome";
        $params_update[':sobrenome'] = $novo_sobrenome;
        $campos_update[] = "email_cli = :email";
        $params_update[':email'] = $novo_email;
        $campos_update[] = "telefone_cli = :telefone";
        $params_update[':telefone'] = $novo_telefone;
        $campos_update[] = "dtNasc_cli = :dtNasc";
        $params_update[':dtNasc'] = $novo_dtNasc;
        $campos_update[] = "cep_cli = :cep";
        $params_update[':cep'] = $novo_cep;
        $campos_update[] = "endereco_rua_cli = :rua";
        $params_update[':rua'] = $nova_rua;
        $campos_update[] = "endereco_cidade_cli = :cidade";
        $params_update[':cidade'] = $nova_cidade;
        $campos_update[] = "endereco_estado_cli = :estado";
        $params_update[':estado'] = $novo_estado;


        // =========================================================
        // 2.2. Lógica de Atualização/Remoção da Foto
        // =========================================================
        $remover_foto = filter_input(INPUT_POST, 'remover_foto', FILTER_VALIDATE_INT);

        if ($remover_foto === 1 && $has_foto) {
            // Caso 1: O usuário clicou em "Remover Foto"
            $campos_update[] = "foto_perfil = NULL";
            // $has_foto deve ser atualizada para que o placeholder seja exibido após o POST, caso haja erro no banco
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

            $campos_update[] = "foto_perfil = :foto_perfil";
            $params_update[':foto_perfil'] = $foto_binario;
        }

        // Se não houver campos para atualizar, não faz o UPDATE
        if (empty($campos_update)) {
            $mensagem_status = 'Nenhuma alteração válida detectada.';
            goto fim_update;
        }

        // 2.3. Executa o UPDATE
        $sql_update = "UPDATE clientes SET " . implode(', ', $campos_update) . " WHERE idcliente = :id";

        try {
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute($params_update);

            $mensagem_status = 'Perfil atualizado com sucesso!';
            $sucesso = true;

            // Redireciona para o perfil original após sucesso
            header("Location: perfil.php?status=sucesso");
            exit;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                // Erro de duplicidade (provavelmente e-mail)
                $mensagem_status = 'Erro: O e-mail informado já está em uso por outro usuário.';
            } else {
                error_log("Erro no UPDATE do cliente: " . $e->getMessage());
                $mensagem_status = 'Erro interno ao atualizar perfil. Tente novamente.';
            }
            // Se houver erro, os campos são preenchidos novamente no formulário para revisão
            $nome_cli = $novo_nome;
            $sobrenome_cli = $novo_sobrenome;
            $email_cli = $novo_email;
            $telefone_cli = $novo_telefone;
            $dtNasc_cli = $novo_dtNasc;
            $cep_cli = $novo_cep;
            $rua_cli = $nova_rua;
            $cidade_cli = $nova_cidade;
            $estado_cli = $novo_estado;
        }
    }
}

fim_update:
// Se o POST falhou, os dados de $cliente_data já foram sobrescritos com os valores do POST no bloco acima.
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
    <title>Editar Perfil - ReservAI</title>
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
            <h1>Editar Perfil</h1>
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
                        src="<?php echo $has_foto ? $foto_base64 : 'https://placehold.co/120x120/d76a03/ffffff?text=' . substr($nome_cli, 0, 1); ?>"
                        alt="Foto de Perfil"
                        class="profile-picture">

                    <label for="foto_perfil_input" class="camera-icon">
                        <img src="img/Icone Camera.png" alt="Trocar Foto">
                    </label>
                    <input type="file" id="foto_perfil_input" name="foto" accept="image/*" onchange="previewImage(event)">
                </div>

                <input type="hidden" id="remover_foto_input" name="remover_foto" value="0">
                <br>
                <?php if ($has_foto) : ?>
                    <button type="button" class="btn-remover-foto" id="btnRemoverFoto">Remover Foto</button>
                <?php endif; ?>

            </div>

            <section class="info-section">
                <h3>Informações Pessoais</h3>

                <div class="info-card">
                    <div class="inline-group">
                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" value="<?php echo $nome_cli; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="sobrenome">Sobrenome</label>
                            <input type="text" id="sobrenome" name="sobrenome" value="<?php echo $sobrenome_cli; ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="<?php echo $email_cli; ?>" required>
                    </div>

                    <div class="inline-group">
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="tel" id="telefone" name="telefone" value="<?php echo $telefone_cli; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="dataNascimento">Data de Nascimento</label>
                            <input type="date" id="dataNascimento" name="dataNascimento" value="<?php echo $dtNasc_cli; ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cpf">CPF (Não pode ser alterado)</label>
                        <input type="text" id="cpf" name="cpf" value="<?php echo $cpf_cli; ?>" disabled>
                    </div>
                </div>
            </section>

            <section class="info-section">
                <h3>Endereço</h3>

                <div class="info-card">
                    <div class="form-group">
                        <label for="cep">CEP</label>
                        <input type="text" id="cep" name="cep" value="<?php echo $cep_cli; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="rua">Rua</label>
                        <input type="text" id="rua" name="rua" value="<?php echo $rua_cli; ?>" required>
                    </div>

                    <div class="inline-group">
                        <div class="form-group">
                            <label for="cidade">Cidade</label>
                            <input type="text" id="cidade" name="cidade" value="<?php echo $cidade_cli; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado (UF)</label>
                            <input type="text" id="estado" name="estado" maxlength="2" value="<?php echo $estado_cli; ?>" required>
                        </div>
                    </div>
                </div>
            </section>

            <button type="submit" class="btn-salvar">Salvar Alterações</button>

        </form>

    </main>

    <br><br><br>
    <br><br><br>


    <!-- ============================================================== 
    NAVBAR COMPLETA
    ============================================================== -->
    <div class="overlay" id="overlay"></div>

    <div class="search-container" id="searchBar">
        <form id="searchForm">
            <input type="search" id="searchInput" placeholder="Pesquisar...">
            <button type="submit">
                <img src="img/Icone Lupa.png" alt="Pesquisar">
            </button>
        </form>
    </div>

    <nav class="navbar">
        <a href="index.php" class="desativo-hover"><img src="img/Icone Casa.png" class="img-nav" alt="Home"></a>
        <a href="<?php echo isset($_SESSION['usuario_id']) ? 'agenda.php' : 'cadastroClientePt1.html'; ?>" class="desativo-hover"><img src="img/Icone Agenda.png" class="img-nav" alt="Agenda"></a>

        <a href="#" class="search-btn" id="openSearch">
            <img src="img/Icone Lupa.png" class="img-lupa-nav" alt="Pesquisar">
            <img src="img/Icone X.png" class="close-icon" alt="Fechar">
        </a>

        <a href="#" class="desativo-hover"><img src="img/Icone Configurações.png" class="img-nav" alt="Configurações"></a>

        <a href="<?php echo isset($_SESSION['usuario_id']) ? 'perfil.php' : 'gestor-cliente.html'; ?>" class="ativo-hover">
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
  FUNÇÃO DE VALIDAÇÃO DE IDADE (18+)
  ==============================================================*/
    function validarIdadeMinima() {
        const dataNascimentoInput = document.getElementById('dataNascimento');
        const dataNascimento = new Date(dataNascimentoInput.value);

        // Se o campo estiver vazio, a validação HTML 'required' já deve tratar isso.
        if (!dataNascimentoInput.value) {
            return true;
        }

        const dataAtual = new Date();
        // Calcula a data mínima de 18 anos atrás
        const dataMinima = new Date(
            dataAtual.getFullYear() - 18,
            dataAtual.getMonth(),
            dataAtual.getDate()
        );

        if (dataNascimento > dataMinima) {
            alert("Você deve ter pelo menos 18 anos para se cadastrar. Verifique sua data de nascimento.");
            dataNascimentoInput.focus();
            return false;
        }

        return true;
    }

    /*============================================================== 
    INTEGRAÇÃO VIACEP
    ==============================================================*/
    const cepInput = document.getElementById('cep');
    const ruaInput = document.getElementById('rua');
    const cidadeInput = document.getElementById('cidade');
    const estadoInput = document.getElementById('estado');

    function limparFormularioEndereco() {
        ruaInput.value = "";
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
        cidadeInput.value = "Buscando...";
        estadoInput.value = "Buscando...";

        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();

            if (!data.erro) {
                ruaInput.value = data.logradouro;
                cidadeInput.value = data.localidade;
                estadoInput.value = data.uf;

                // Move o foco para a rua, caso não tenha sido preenchida (pode ser CEP de logradouro único)
                if (data.logradouro.trim() === "") {
                    ruaInput.focus();
                } else {
                    // Se a rua veio preenchida, move o foco para o próximo campo lógico
                    // (Exemplo: Número, se houvesse, ou cidade/estado)
                    // Como não temos 'numero' no form, foca no nome
                    document.getElementById('nome').focus();
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
      SUBMISSÃO GERAL DO FORMULÁRIO (PARA CAPTURAR O BOTÃO SALVAR)
      ==============================================================*/
    const formEdicao = document.getElementById('formEdicao');

    // Adiciona o listener para o evento de submissão do formulário
    formEdicao.addEventListener('submit', function(e) {
        // 1. Executa a validação de idade
        if (!validarIdadeMinima()) {
            e.preventDefault(); // Impede a submissão se a idade for inválida
            return;
        }

        // Se a idade for válida, a submissão continua normalmente.
    });


    /*============================================================== 
        BOTÃO REMOVER FOTO
        ==============================================================*/
    const btnRemoverFoto = document.getElementById('btnRemoverFoto');
    const removerFotoInput = document.getElementById('remover_foto_input');

    if (btnRemoverFoto) {
        btnRemoverFoto.addEventListener('click', function(e) {
            e.preventDefault();

            if (confirm('Tem certeza que deseja remover sua foto de perfil?')) {
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

    /*==============================================================
    SCRIPT NAVBAR (Replicado do perfil.php)
    ==============================================================*/
    const openSearch = document.getElementById("openSearch");
    const searchBar = document.getElementById("searchBar");
    const searchInput = document.getElementById("searchInput");
    const overlay = document.getElementById("overlay");

    function abrirPesquisa() {
        openSearch.classList.add("active");
        searchBar.style.display = "block";
        overlay.style.display = "block";
        setTimeout(() => {
            overlay.style.opacity = "1";
            searchInput.focus();
        }, 10);
    }

    function fecharPesquisa() {
        openSearch.classList.remove("active");
        overlay.style.opacity = "0";

        setTimeout(() => {
            overlay.style.display = "none";
            searchBar.style.display = "none";
        }, 300);
    }

    openSearch.addEventListener("click", (e) => {
        e.preventDefault();
        if (openSearch.classList.contains("active")) {
            fecharPesquisa();
        } else {
            abrirPesquisa();
        }
    });

    document.addEventListener("click", (e) => {
        if (searchBar.style.display === "block") {
            if (!searchBar.contains(e.target) && !openSearch.contains(e.target)) {
                fecharPesquisa();
            }
        }
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && searchBar.style.display === "block") {
            fecharPesquisa();
        }
    });

    overlay.addEventListener("click", fecharPesquisa);
</script>

</html>