<?php
// Inclui a conexão (ajuste o caminho se necessário)
require_once 'backend/conexao.php';
// =======================================================
// ADICIONADO: Inicia a sessão para verificar o login
// =======================================================
session_start();

// Verifica se o ID do restaurante foi passado na URL (Query String)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Erro: ID do restaurante inválido ou não fornecido.");
}

$id_restaurante = (int)$_GET['id'];
$dados_restaurante = null;
$cardapio = [];

try {
    // 1. BUSCAR DADOS DO RESTAURANTE
    $stmt_res = $pdo->prepare("SELECT 
    nome_restaurante, 
    descricao, 
    logo_res, 
    fotoPrincipal_res, 
    foto1_res, 
    foto2_res, 
    foto3_res,
    endereco_rua_res, 
    endereco_num_res, 
    endereco_bairro_res, 
    endereco_cidade_res, 
    endereco_estado_res
    FROM restaurantes WHERE idrestaurante = ?");
    $stmt_res->execute([$id_restaurante]);
    $dados_restaurante = $stmt_res->fetch();

    if (!$dados_restaurante) {
        die("Restaurante não encontrado.");
    }

    // Constrói o endereço completo
    $dados_restaurante['endereco_completo'] = $dados_restaurante['endereco_rua_res'] . ', '
        . $dados_restaurante['endereco_num_res'] . ', '
        . $dados_restaurante['endereco_bairro_res'] . ', '
        . $dados_restaurante['endereco_cidade_res'] . '/'
        . $dados_restaurante['endereco_estado_res'];


    // 2. BUSCAR DADOS DO CARDÁPIO
    $stmt_card = $pdo->prepare("SELECT idcardapio, nome_alimento, preco FROM cardapio WHERE restaurante_id = ?");
    $stmt_card->execute([$id_restaurante]);
    $cardapio = $stmt_card->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar dados do restaurante: " . $e->getMessage());
}

// ======================================================
// LÓGICA DO BOTÃO DE RESERVA
// ======================================================
$esta_logado = isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);

if ($esta_logado) {
    // Se estiver logado, leva para a página de criação de reserva
    $url_destino = 'criarReserva.php?restaurante_id=' . $id_restaurante;
} else {
    // Se NÃO estiver logado, leva para a página de cadastro/login (CORRIGIDO PARA .html)
    $url_destino = 'cadastroClientePt1.html';
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($dados_restaurante['nome_restaurante']); ?> - ReservAI</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="img/Logo.png">
    <link rel="stylesheet" href="src/css/padrão.css">
    <link rel="stylesheet" href="src/css/navbar.css">
    <link rel="stylesheet" href="src/css/reserva.css">

</head>

<body>
    <div class="titulo">
        <img src="img/Icone Voltar.png" alt="Voltar" id="voltar">
        <h1>Criar Reserva</h1>
    </div>

    <div class="cabecalho">
        <img src="backend/exibir_imagem.php?id=<?php echo $id_restaurante; ?>&tipo=logo" alt="Logo do Restaurante" class="logo-restaurante">
        <div class="info-restaurante">
            <h2><?php echo htmlspecialchars($dados_restaurante['nome_restaurante']); ?></h2>
            <p><?php echo htmlspecialchars($dados_restaurante['endereco_completo']); ?></p>
        </div>
    </div>

    <div class="container-slideshow">
        <?php
        $fotos = [];
        // Define as fotos disponíveis
        if (!empty($dados_restaurante['fotoPrincipal_res'])) $fotos[] = 'fotoPrincipal';
        if (!empty($dados_restaurante['foto1_res'])) $fotos[] = 'foto1';
        if (!empty($dados_restaurante['foto2_res'])) $fotos[] = 'foto2';
        if (!empty($dados_restaurante['foto3_res'])) $fotos[] = 'foto3';

        if (empty($fotos)) $fotos[] = 'placeholder';

        $contador_slides = 0;
        foreach ($fotos as $tipo_foto) {
            $contador_slides++;
            $url_imagem = ($tipo_foto === 'placeholder')
                ? 'img/placeholder.jpg'
                : 'backend/exibir_imagem.php?id=' . $id_restaurante . '&tipo=' . $tipo_foto;
        ?>
            <div class="slide fade" style="display: <?php echo $contador_slides === 1 ? 'block' : 'none'; ?>;">
                <img src="<?php echo $url_imagem; ?>">
            </div>
        <?php } ?>

        <a class="anterior" onclick="mudarSlide(-1)">&#10094;</a>
        <a class="proximo" onclick="mudarSlide(1)">&#10095;</a>
    </div>

    <br>

    <div style="text-align:center">
        <?php for ($i = 1; $i <= $contador_slides; $i++) { ?>
            <span class="bolinha" onclick="slideAtual(<?php echo $i; ?>)"></span>
        <?php } ?>
    </div>

    <div class="infoRestaurante">
        <h2>Sobre o Restaurante</h2>
        <p><?php echo nl2br(htmlspecialchars($dados_restaurante['descricao'] ?? 'Descrição não fornecida.')); ?></p>
    </div>

    <div class="botao-reservar-mesa">
        <a href="<?php echo $url_destino; ?>"><button>Criar Reserva<img src="img/Icone Agenda Branco.png"></button></a>
    </div>

    <div class="container-carousel swiper">
        <h2>Cardápio</h2>
        <div class="card-wrapper">
            <ul class="card-list swiper-wrapper">
                <?php foreach ($cardapio as $prato) { ?>
                    <li class="card-item swiper-slide">
                        <div class="card-link">
                            <img src="backend/exibir_cardapio_imagem.php?id=<?php echo $prato['idcardapio']; ?>"
                                alt="<?php echo htmlspecialchars($prato['nome_alimento']); ?>"
                                class="card-image">

                            <h2 class="card-title"><?php echo htmlspecialchars($prato['nome_alimento']); ?></h2>
                            <br>
                            <p>Preço: R$ <?php echo number_format($prato['preco'], 2, ',', '.'); ?></p>
                        </div>
                    </li>
                <?php }
                // Se o cardápio estiver vazio, insere um placeholder para evitar erros no Swiper
                if (empty($cardapio)) { ?>
                    <li class="card-item swiper-slide">
                        <div class="card-link">
                            <p>Cardápio ainda não cadastrado para este restaurante.</p>
                        </div>
                    </li>
                <?php } ?>
            </ul>

            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>
    <br><br><br><br><br>

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

        <a href="<?php echo isset($_SESSION['usuario_id']) ? 'perfil.php' : 'gestor-cliente.html'; ?>" class="desativo-hover">
            <img src="img/Icone Perfil.png" class="img-nav" alt="Perfil">
        </a>

    </nav>

</body>

<script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {

        /* ============================================================== 
        SCRIPT NAVBAR
        ============================================================== */
        const openSearch = document.getElementById("openSearch");
        const searchBar = document.getElementById("searchBar");
        const searchInput = document.getElementById("searchInput");
        const searchForm = document.getElementById("searchForm");
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
            searchBar.style.display = "none";
            overlay.style.opacity = "0";
            setTimeout(() => (overlay.style.display = "none"), 300);
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
            if (
                !searchBar.contains(e.target) &&
                !openSearch.contains(e.target) &&
                searchBar.style.display === "block"
            ) {
                fecharPesquisa();
            }
        });

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && searchBar.style.display === "block") {
                fecharPesquisa();
            }
        });

        overlay.addEventListener("click", fecharPesquisa);

        /* ============================================================== 
        BOTÃO VOLTAR
        ============================================================== */
        document.getElementById('voltar').addEventListener('click', function() {
            history.back();
        });

        /* ============================================================== 
        SCRIPT DO CARROSSEL
        ============================================================== */
        let indiceSlide = 1;
        mostrarSlides(indiceSlide);

        function mudarSlide(n) {
            mostrarSlides(indiceSlide += n);
        }

        function slideAtual(n) {
            mostrarSlides(indiceSlide = n);
        }

        function mostrarSlides(n) {
            // Este script agora é dinâmico com base no PHP
            const slides = document.getElementsByClassName("slide");
            const bolinhas = document.getElementsByClassName("bolinha");
            if (slides.length === 0) return;

            if (n > slides.length) {
                indiceSlide = 1
            }
            if (n < 1) {
                indiceSlide = slides.length
            }
            for (let i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            for (let i = 0; i < bolinhas.length; i++) {
                bolinhas[i].className = bolinhas[i].className.replace(" ativo", "");
            }
            slides[indiceSlide - 1].style.display = "block";
            bolinhas[indiceSlide - 1].className += " ativo";
        }

        // Torna funções acessíveis no HTML
        window.mudarSlide = mudarSlide;
        window.slideAtual = slideAtual;
    });


    /* ============================================================== 
    CARDÁPIO
    ============================================================== */


    new Swiper('.card-wrapper', {
        loop: true,

        pagination: {
            el: '.swiper-pagination',
        },

        // Navigation arrows
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },

        breakpoints: {
            0: {
                slidesPerView: 1,
            },
            768: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 20,
            }

        }
    });
</script>

</html>