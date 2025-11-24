<?php
// =========================================================
// 1. INICIA A SESSÃO
// =========================================================
session_start();
require_once "backend/conexao.php";

// =========================================================
// 2. VERIFICAÇÃO DE LOGIN E OBTENÇÃO DO ID
// =========================================================
// Verifica se o ID do usuário (cliente) está na sessão. Se não estiver, redireciona.
if (empty($_SESSION['usuario_id'])) {
    // Redireciona para a página de cadastro/login
    header("Location: cadastroClientePt1.php");
    exit;
}

// OBTENÇÃO DO ID DO CLIENTE (AGORA VEM DA SESSÃO)
$cliente_id = $_SESSION['usuario_id'];
// =========================================================


// Consulta SQL CORRIGIDA (SEM REFERÊNCIA À TABELA 'mesas')
$sql = "
  SELECT 
    r.idreserva,
    r.numero_clientes,
    r.data_reserva,
    r.horario_inicio,
    r.horario_fim,
    r.status,
    r.restaurante_id,
    res.nome_restaurante,
    res.logo_res
  FROM reservas r
  LEFT JOIN restaurantes res ON r.restaurante_id = res.idrestaurante
  WHERE r.cliente_id = :cliente_id -- FILTRA as reservas apenas do cliente logado
  -- Exclui reservas canceladas ou finalizadas para exibir apenas as ativas
  AND r.status = 'confirmada' 
  ORDER BY r.data_reserva ASC, r.horario_inicio ASC
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT); // Faz o bind do parâmetro para segurança
$stmt->execute();
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/Logo.png">
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
            <h2 class="sem-reserva" style="color: #d76a03; font-size: 30px; text-align: center; margin-top: 100px; margin-bottom: 0px;">Que tal fazer a primeira?</h2>
            <img class="sem-reserva-img" src="img/cozinheiro.png" alt="Cozinheiro Sugerindo Reserva">
        <?php else: ?>
            <?php foreach ($reservas as $r):
                // Monta a URL da imagem usando o ID do restaurante
                $url_logo = 'backend/exibir_imagem.php?id=' . $r['restaurante_id'] . '&tipo=logo';
            ?>
                <div class="card-reserva">

                    <div class="card-header">
                        <img src="<?= $url_logo ?>" class="card-logo" alt="Logo do Restaurante">
                        <h2><?= htmlspecialchars($r['nome_restaurante']) ?></h2>
                    </div>

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

                        <p><strong>Status:</strong>
                            Reserva Ativa
                        </p>
                    </div>

                    <button
                        class="btn-cancelar"
                        type="button"
                        data-reserva-id="<?= $r['idreserva'] ?>">
                        Cancelar
                    </button>


                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>


    <div class="modal-confirmacao" id="modalCancelamento">
        <div class="modal-content">
            <h3>Confirmar Cancelamento</h3>
            <p>Tem certeza que deseja cancelar esta reserva? Esta ação não pode ser desfeita.</p>
            <img src="img/cozinheiro triste.png" alt="Cozinheiro Triste">
            <div class="modal-botoes">
                <button class="btn-sim-confirmar" id="btnConfirmarCancelamento">Sim, Cancelar</button>
                <button class="btn-nao-cancelar" id="btnFecharModal">Não, Manter</button>
            </div>
        </div>
    </div>


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
        <a href="<?php echo isset($_SESSION['usuario_id']) ? 'agenda.php' : 'cadastroClientePt1.html'; ?>" class="ativo-hover"><img src="img/Icone Agenda.png" class="img-nav" alt="Agenda"></a>

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
    /* Variável global para armazenar o ID da reserva a ser cancelada */
    let reservaIdParaCancelar = null;

    /* Elementos do Modal */
    const modal = document.getElementById('modalCancelamento');
    const btnConfirmar = document.getElementById('btnConfirmarCancelamento');
    const btnFecharModal = document.getElementById('btnFecharModal');

    // ============================================================== 
    // FUNÇÕES DO MODAL DE CANCELAMENTO
    // ============================================================== 
    function abrirModal(reservaId) {
        reservaIdParaCancelar = reservaId;
        modal.classList.add('show');
    }

    function fecharModal() {
        modal.classList.remove('show');
        reservaIdParaCancelar = null;
    }

    // Adiciona listener aos botões Cancelar em cada card
    document.querySelectorAll('.btn-cancelar').forEach(button => {
        button.addEventListener('click', function() {
            // Captura o ID da reserva do atributo data-reserva-id
            const reservaId = this.getAttribute('data-reserva-id');
            if (reservaId) {
                abrirModal(reservaId);
            }
        });
    });

    // Listener para o botão "Não, Manter" no modal
    btnFecharModal.addEventListener('click', fecharModal);

    // Listener para o botão "Sim, Cancelar" no modal
    btnConfirmar.addEventListener('click', function() {
        if (reservaIdParaCancelar) {
            // Redireciona para o script backend de cancelamento
            window.location.href = `backend/cancelarReserva.php?id=${reservaIdParaCancelar}`;
        }
    });

    // Fecha o modal se clicar fora dele (clicando no fundo escuro)
    modal.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-confirmacao')) {
            fecharModal();
        }
    });

    // ============================================================== 
    // SCRIPT NAVBAR (MANTIDO)
    // ============================================================== 
    const openSearch = document.getElementById("openSearch");
    const searchBar = document.getElementById("searchBar");
    const searchInput = document.getElementById("searchInput");
    const searchForm = document.getElementById("searchForm");
    const overlay = document.getElementById("overlay"); // O overlay agora também serve como fundo para o modal

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
        if (e.key === "Escape") {
            // Fecha a pesquisa E o modal, se abertos
            if (searchBar.style.display === "block") {
                fecharPesquisa();
            }
            if (modal.classList.contains('show')) {
                fecharModal();
            }
        }
    });

    overlay.addEventListener("click", fecharPesquisa);

    /* ============================================================== 
    BOTÃO VOLTAR
    ============================================================== */
    document.getElementById('voltar').addEventListener('click', function() {
        history.back();
    });
</script>

</html>