    <?php
    // Inclui a conexão (ajuste o caminho se necessário)
    require_once 'backend/conexao.php';

    // Verifica se o ID do restaurante foi passado na URL (Query String)
    if (!isset($_GET['restaurante_id']) || !is_numeric($_GET['restaurante_id'])) {
        die("Erro: ID do restaurante inválido ou não fornecido.");
    }

    $id_restaurante = (int)$_GET['restaurante_id'];
    $dados_restaurante = null;

    try {
        // BUSCAR DADOS DO RESTAURANTE
        $stmt_res = $pdo->prepare("SELECT 
            nome_restaurante, 
            logo_res, 
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
    } catch (PDOException $e) {
        die("Erro ao carregar dados do restaurante: " . $e->getMessage());
    }
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" type="image/x-icon" href="img/Logo.png">
        <link rel="stylesheet" href="src/css/padrão.css">
        <link rel="stylesheet" href="src/css/navbar.css">
        <link rel="stylesheet" href="src/css/criarReserva.css">
        <title>Criar Reserva - <?php echo htmlspecialchars($dados_restaurante['nome_restaurante']); ?></title>
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


        <div class="formulario-reserva">
            <form action="backend/criarReserva.php" method="post">
                <input type="hidden" name="restaurante_id" value="<?php echo $id_restaurante; ?>">

                <label for="pessoas">Quantidade de Pessoas <span class="obrigatorio">*</span></label>

                <select id="pessoas" name="pessoas" required>
                    <option value="" disabled selected>Quantidade de Pessoas</option>
                    <option value="1">1 Pessoa</option>
                    <option value="2">2 Pessoas</option>
                    <option value="3">3 Pessoas</option>
                    <option value="4">4 Pessoas</option>
                    <option value="5">5 Pessoas</option>
                    <option value="6">6 Pessoas</option>
                    <option value="7">7 Pessoas</option>
                    <option value="8">8 Pessoas</option>
                </select>
                <label for="data">Data <span class="obrigatorio">*</span></label>
                <input type="date" id="data" name="data" required>

                <label for="horario">Horário <span class="obrigatorio">*</span></label>
                <input type="time" id="horario" name="horario" min="10:00" max="21:00" step="1800" required>

                <button type="submit" class="btnConfirmar">CONFIRMAR</button>
                <button type="button" class="btnCancelar" onclick="window.history.back()">CANCELAR</button>

            </form>
        </div>


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
    <script>
        /* ============================================================== 
        SCRIPT NAVBAR
            ==============================================================  */
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
        ==============================================================  */
        document.getElementById('voltar').addEventListener('click', function() {
            history.back();
        });

        /* ============================================================== 
        DATA MINIMA
        ==============================================================  */
        const inputData = document.getElementById('data');
        const hoje = new Date();
        hoje.setDate(hoje.getDate()); // Amanhã
        const ano = hoje.getFullYear();
        const mes = String(hoje.getMonth() + 1).padStart(2, '0');
        const dia = String(hoje.getDate()).padStart(2, '0');
        const minData = `${ano}-${mes}-${dia}`;
        inputData.min = minData;
    </script>

    </html>