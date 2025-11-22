<?php
// =========================================================
// 1. INICIA A SESSÃO, CONEXÃO E OBTÉM DADOS DO CLIENTE
// =========================================================
session_start();
// O arquivo "backend/conexao.php" é necessário para a conexão PDO.
// Este arquivo deve conter a variável $pdo com a conexão ativa.
require_once "backend/conexao.php";

if (empty($_SESSION['usuario_id'])) {
    // Redireciona se o usuário não estiver logado
    header("Location: cadastroClientePt1.php");
    exit;
}

$cliente_id = $_SESSION['usuario_id'];
$cliente_data = [];

// 1.2. Busca os dados completos do cliente
$sql = "SELECT 
            nome_cli, 
            email_cli, 
            telefone_cli,
            endereco_rua_cli, 
            endereco_cidade_cli, 
            endereco_estado_cli
        FROM clientes 
        WHERE idcliente = :id";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $cliente_id]);
    $cliente_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente_data) {
        // Se o ID da sessão não corresponder a nenhum cliente, desloga e redireciona
        session_destroy();
        header("Location: cadastroClientePt1.php?erro=usuario_nao_encontrado");
        exit;
    }
} catch (PDOException $e) {
    // Registra o erro detalhado e exibe uma mensagem genérica
    error_log("Erro ao buscar dados do cliente: " . $e->getMessage());
    die("Erro interno ao carregar perfil. Por favor, tente novamente.");
}

// 1.3. Define as variáveis para uso no HTML (com valores padrão e sanitização)
$nome = htmlspecialchars($cliente_data['nome_cli'] ?? 'Nome do Cliente');
$email = htmlspecialchars($cliente_data['email_cli'] ?? 'email@exemplo.com');
$telefone = htmlspecialchars($cliente_data['telefone_cli'] ?? '(00) 00000-0000');
$rua = htmlspecialchars($cliente_data['endereco_rua_cli'] ?? 'Rua de Exemplo');

// Valores padrão para campos que NÃO existem na tabela (endereco_num_cli e endereco_bairro_cli)
$numero = 'N/A';
$bairro = 'N/A';

$cidade = htmlspecialchars($cliente_data['endereco_cidade_cli'] ?? 'Cidade');
$estado = htmlspecialchars($cliente_data['endereco_estado_cli'] ?? 'UF');

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/Logo.png">
    <link rel="stylesheet" href="src/css/padrão.css">
    <link rel="stylesheet" href="src/css/navbar.css">

    <title>Perfil - ReservAI</title>

    <!-- ======================================== -->
    <!-- ESTILOS ESPECÍFICOS DO PERFIL (Inline) -->
    <!-- Isso garante que o CSS funcione sem problemas de caminho de arquivo -->
    <!-- ======================================== -->
    <style>
        /* ======================================== */
        /* ESTILOS DA BARRA DE TOPO (Header) - Mantido do código base */
        /* ======================================== */
        .barra-topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 5vw;
            margin-top: 40px;
            background-color: #FFF5EA;
            /* Cor de fundo principal */
        }

        .esquerda {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .esquerda h1 {
            font-size: clamp(24px, 4vw, 40px);
            color: #D76A03;
            margin: 0;
        }

        .icone-voltar {
            width: 28px;
            height: 28px;
            cursor: pointer;
        }

        .direita {
            display: flex;
            align-items: center;
        }

        .icone-editar {
            width: 32px;
            height: 32px;
            margin-right: 10px;
            cursor: pointer;
        }

        /* ======================================== */
        /* CONTEÚDO PRINCIPAL (Profile) */
        /* ======================================== */
        .perfil-container {
            width: 90%;
            max-width: 500px;
            /* Limita a largura do conteúdo principal */
            margin: 10px auto 100px auto;
            /* Centraliza e adiciona espaço para a navbar */
            padding: 0;
        }

        /* --- HEADER DO PERFIL (Foto, Nome, Botão Gerenciar) --- */
        .perfil-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-picture-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #D76A03;
            /* Borda laranja */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .camera-icon {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #fff;
            border: 2px solid #D76A03;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .camera-icon img {
            width: 18px;
            height: 18px;
        }

        .nome-cliente {
            font-size: 24px;
            color: #3f2100;
            margin-bottom: 20px;
        }

        /* --- BOTÃO GERENCIAR CONTA --- */
        .btn-gerenciar {
            display: inline-flex;
            align-items: center;
            background-color: #fff;
            color: #D76A03;
            padding: 10px 20px;
            border-radius: 25px;
            border: 2px solid #D76A03;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            transition: background-color 0.2s;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-gerenciar:hover {
            background-color: #f7e6d5;
        }

        .btn-gerenciar img {
            width: 18px;
            height: 18px;
            /* Filtro para converter a cor do ícone de Configurações para Laranja (#D76A03) */
            filter: invert(47%) sepia(35%) saturate(3062%) hue-rotate(345deg) brightness(85%) contrast(85%);
        }

        /* ======================================== */
        /* SEÇÕES DE INFORMAÇÃO */
        /* ======================================== */
        .info-section {
            margin-bottom: 10px;
        }

        .info-section h3 {
            text-align: left;
            font-size: 20px;
            color: #D76A03;
            margin-bottom: 15px;
            padding-left: 5px;
            /* Alinhamento visual */
        }

        .info-card {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            text-align: left;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            background: transparent;
        }

        .info-item:last-child {
            border-bottom: none;
            /* Remove separador do último item */
            padding-bottom: 0;
        }

        .label {
            font-size: 12px;
            color: #653400;
            /* Marrom escuro */
            font-weight: 500;
            margin-bottom: 4px;
            background: transparent;
        }

        .valor {
            font-size: 16px;
            color: #3f2100;
            background: transparent;
            font-weight: bold;
            margin: 0;
        }

        /* --- BOTÃO SAIR --- */
        .btn-sair {
            display: block;
            width: 100%;
            background-color: #bf3100;
            /* Vermelho para sair */
            color: white;
            padding: 12px 0;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            font-size: 17px;
            margin-top: 30px;
            transition: background-color 0.2s;
        }

        .btn-sair:hover {
            background-color: #942500ff;
        }
    </style>
</head>

<body>
    <!-- =============================== -->
    <!-- TÍTULO DA PÁGINA (Barra Topo) -->
    <!-- =============================== -->
    <div class="barra-topo">

        <div class="esquerda">
            <!-- Funcionalidade de voltar via JS no final do arquivo -->
            <img src="img/Icone Voltar.png" alt="Voltar" id="voltar" class="icone-voltar">
            <h1>Perfil</h1>
        </div>

        <div class="direita">
            <!-- O ícone de lápis para editar, redirecionando para uma página de edição -->
            <a href="editarPerfil.php">
                <img src="img/Lapis.png" alt="Editar" class="icone-editar">
            </a>
        </div>

    </div>

    <!-- =============================== -->
    <!-- CONTEÚDO PRINCIPAL DO PERFIL -->
    <!-- =============================== -->
    <main class="perfil-container">

        <!-- Foto de Perfil e Nome -->
        <div class="perfil-header">
            <div class="profile-picture-wrapper">
                <!-- Placeholder: Exibe a primeira letra do nome (com sanitização) -->
                <img src="https://placehold.co/120x120/d76a03/ffffff?text=<?php echo substr($nome, 0, 1); ?>" alt="Foto de Perfil" class="profile-picture">
                <!-- Ícone da Câmera (para upload/edição) -->
                <a href="editarPerfil.php" class="camera-icon">
                    <img src="img/Icone Camera.png" alt="Trocar Foto">
                </a>
            </div>
            <h2 class="nome-cliente"><?php echo $nome; ?></h2>

        </div>

        <!-- Informações Pessoais -->
        <section class="info-section">
            <h3>Informações Pessoais</h3>

            <div class="info-card">
                <!-- Campo NOME -->
                <div class="info-item">
                    <span class="label">Nome</span>
                    <p class="valor"><?php echo $nome; ?></p>
                </div>

                <!-- Campo E-MAIL -->
                <div class="info-item">
                    <span class="label">E-mail</span>
                    <p class="valor"><?php echo $email; ?></p>
                </div>

                <!-- Campo TELEFONE -->
                <div class="info-item">
                    <span class="label">Telefone</span>
                    <p class="valor"><?php echo $telefone; ?></p>
                </div>
            </div>
        </section>

        <!-- Informações de Endereço -->
        <section class="info-section">
            <h3>Endereço</h3>

            <div class="info-card">
                <!-- Campo RUA e NÚMERO (Número como 'N/A' pois não está no DDL) -->
                <div class="info-item">
                    <span class="label">Rua / Número</span>
                    <p class="valor"><?php echo $rua; ?></p>
                </div>

                <!-- Campo CIDADE e ESTADO -->
                <div class="info-item">
                    <span class="label">Cidade / Estado</span>
                    <p class="valor"><?php echo $cidade . " - " . $estado; ?></p>
                </div>
            </div>
        </section>

        <!-- Botão Sair - Assumindo que backend/logout.php existe -->
        <a href="backend/logout.php" class="btn-sair">Sair</a>

    </main>


    <!-- ==============================================================
    NAVBAR COMPLETA
    ==============================================================-->

    <!-- Overlay escuro -->
    <div class="overlay" id="overlay"></div>

    <!-- Barra de pesquisa -->
    <div class="search-container" id="searchBar">
        <form id="searchForm">
            <input type="search" id="searchInput" placeholder="Pesquisar...">
            <button type="submit">
                <img src="img/Icone Lupa.png" alt="Pesquisar">
            </button>
        </form>
    </div>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.php" class="desativo-hover"><img src="img/Icone Casa.png" class="img-nav" alt="Home"></a>
        <a href="agenda.php" class="desativo-hover"><img src="img/Icone Agenda.png" class="img-nav" alt="Agenda"></a>

        <a href="#" class="search-btn" id="openSearch">
            <img src="img/Icone Lupa.png" class="img-lupa-nav" alt="Pesquisar">
            <img src="img/Icone X.png" class="close-icon" alt="Fechar">
        </a>

        <a href="configuracoes.php" class="desativo-hover"><img src="img/Icone Configurações.png" class="img-nav"
                alt="Configurações"></a>

        <!-- Botão de perfil ativo -->
        <a href="perfil.php" class="ativo-hover">
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
    SCRIPT NAVBAR (Funcionalidade de Pesquisa e Overlay)
    ==============================================================*/
    const openSearch = document.getElementById("openSearch");
    const searchBar = document.getElementById("searchBar");
    const searchInput = document.getElementById("searchInput");
    const searchForm = document.getElementById("searchForm");
    const overlay = document.getElementById("overlay"); // Já existe no HTML

    // Função para abrir a pesquisa
    function abrirPesquisa() {
        openSearch.classList.add("active");
        searchBar.style.display = "block";
        overlay.style.display = "block";
        setTimeout(() => {
            overlay.style.opacity = "1";
            searchInput.focus();
        }, 10);
    }

    // Função para fechar a pesquisa
    function fecharPesquisa() {
        openSearch.classList.remove("active");
        overlay.style.opacity = "0";

        // Define um timeout para esconder os elementos APÓS a transição do overlay
        setTimeout(() => {
            overlay.style.display = "none";
            searchBar.style.display = "none";
        }, 300); // 300ms deve corresponder à duração da transição CSS
    }

    // Alterna visibilidade da barra
    openSearch.addEventListener("click", (e) => {
        e.preventDefault();
        if (openSearch.classList.contains("active")) {
            fecharPesquisa();
        } else {
            abrirPesquisa();
        }
    });

    // Fecha se clicar fora da barra de pesquisa e do botão
    document.addEventListener("click", (e) => {
        if (searchBar.style.display === "block") {
            if (!searchBar.contains(e.target) && !openSearch.contains(e.target)) {
                fecharPesquisa();
            }
        }
    });

    // Fecha com ESC
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && searchBar.style.display === "block") {
            fecharPesquisa();
        }
    });

    // Fecha ao clicar no overlay
    overlay.addEventListener("click", fecharPesquisa);
    /*==============================================================
    FIM SCRIPT NAVBAR
    ==============================================================*/
</script>

</html>