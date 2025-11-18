<?php
require_once "backend/conexao.php";

// Consulta com JOIN para trazer dados úteis
$sql = "
    SELECT 
        r.idreserva,
        r.numero_clientes,
        r.data_reserva,
        r.horario_inicio,
        r.horario_fim,
        r.status,
        res.nome_restaurante,
        m.numero AS mesa_numero
    FROM reservas r
    LEFT JOIN restaurantes res ON r.restaurante_id = res.idrestaurante
    LEFT JOIN mesas m ON r.mesa_id = m.idmesa
    ORDER BY r.data_reserva ASC, r.horario_inicio ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - Reservas</title>

    <link rel="stylesheet" href="src/css/padrão.css">
    <link rel="stylesheet" href="src/css/navbar.css">
    <link rel="stylesheet" href="src/css/agenda.css">
</head>

<body>

    <div class="titulo">
        <img src="img/Icone Voltar.png" alt="Voltar" id="voltar">
        <h1>Agenda</h1>
    </div>

    <div class="lista-reservas">
        <?php if (count($reservas) === 0): ?>
            <p class="sem-reserva">Nenhuma reserva encontrada.</p>
        <?php else: ?>
            <?php foreach ($reservas as $r): ?>
                <div class="card-reserva">

                    <!-- Cabeçalho do card com logo + nome -->
                    <div class="card-header">
                        <img src="img/restaurante_padrao.jpg" class="card-logo" alt="Logo">
                        <h2><?= htmlspecialchars($r['nome_restaurante']) ?></h2>
                    </div>

                    <!-- Bloco de informações -->
                    <div class="card-info">
                        <p><strong>Data:</strong> <?= date("d/m/Y", strtotime($r['data_reserva'])) ?></p>
                        <hr>

                        <p><strong>Horário:</strong>
                            <?= substr($r['horario_inicio'], 0, 5) ?>
                            às
                            <?= substr($r['horario_fim'], 0, 5) ?>
                        </p>
                        <hr>

                        <p><strong>Clientes:</strong> <?= $r['numero_clientes'] ?></p>
                        <hr>

                        <p><strong>Mesa:</strong>
                            <?= $r['mesa_numero'] !== null ? $r['mesa_numero'] : "Não definida" ?>
                        </p>
                    </div>

                        <button class="btn-cancelar" type="submit">Cancelar</button>



                </div>
            <?php endforeach; ?>
        <?php endif; ?>
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
        <a href="agenda.php" class="ativo-hover"><img src="img/Icone Agenda.png" class="img-nav" alt="Agenda"></a>

        <a href="#" class="search-btn" id="openSearch">
            <img src="img/Icone Lupa.png" class="img-lupa-nav" alt="Pesquisar">
            <img src="img/Icone X.png" class="close-icon" alt="Fechar">
        </a>

        <a href="#" class="desativo-hover"><img src="img/Icone Configurações.png" class="img-nav"
                alt="Configurações"></a>
        <a href="#" class="desativo-hover"><img src="img/Icone Perfil.png" class="img-nav" alt="Perfil"></a>
    </nav>

</body>
<script>
    /*  ============================================================== 
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

    /*  ============================================================== 
    BOTÃO VOLTAR
    ==============================================================  */
    document.getElementById('voltar').addEventListener('click', function() {
        history.back();
    });

</script>

</html>