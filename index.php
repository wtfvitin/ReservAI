<?php
session_start();
require_once "backend/conexao.php";

$cidadeCliente = "Sua cidade";
$estadoCliente = "UF";

if (isset($_SESSION['usuario_id'])) {
  $id = $_SESSION['usuario_id']; // Linha 8
  $sql = "SELECT endereco_cidade_cli, endereco_estado_cli FROM clientes WHERE idcliente = :id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':id' => $id]);
  $dados = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($dados) {
    $cidadeCliente = $dados['endereco_cidade_cli'];
    $estadoCliente = $dados['endereco_estado_cli'];
  }
};
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" type="image/x-icon" href="img/Logo.png">
  <link rel="stylesheet" href="src/css/padrão.css">
  <link rel="stylesheet" href="src/css/navbar.css">
  <link rel="stylesheet" href="src/css/index.css">
  <title>Início - ReservAI</title>

</head>

<body>

  <div class="titulo">
    <div class="titulo-esquerda">
      <h1>Reserve Aí!</h1>
      <img src="img/Table.png" alt="Mesa de Restaurante">
    </div>

    <div class="localizacao">
      <img src="img/Icone Localizacao.png" alt="Localização" class="icone-localizacao">
      <span class="texto-localizacao"> <?php echo htmlspecialchars($cidadeCliente . ", " . $estadoCliente); ?></span>
    </div>
  </div>
  <div class="content">
    <?php
    // Busca todos os restaurantes cadastrados
    $sql = $pdo->query("SELECT * FROM restaurantes ORDER BY idrestaurante DESC");

    if ($sql->rowCount() == 0) {
      echo "<p style='width:100%; text-align:center; margin-top:20px; font-size:18px;'>Nenhum restaurante cadastrado ainda.</p>";
    }

    while ($rest = $sql->fetch(PDO::FETCH_ASSOC)):
    ?>

      <div class="card">
        <img src="backend/exibir_imagem.php?id=<?= $rest['idrestaurante'] ?>&tipo=fotoPrincipal" alt="Capa do Restaurante">

        <div class="card-content">
          <h2><?= htmlspecialchars($rest['nome_restaurante']) ?></h2>

          <p>
            <?= htmlspecialchars($rest['endereco_rua_res']) ?>,
            <?= htmlspecialchars($rest['endereco_num_res']) ?> -
            <?= htmlspecialchars($rest['endereco_bairro_res']) ?><br>
            <?= htmlspecialchars($rest['endereco_cidade_res']) ?> -
            <?= htmlspecialchars($rest['endereco_estado_res']) ?>
          </p>

          <a href="reserva.php?id=<?= $rest['idrestaurante'] ?>">
            <button>Reserve já</button>
          </a>
        </div>
      </div>

    <?php endwhile; ?>
  </div>

  <br><br><br>
  <br><br><br>

  <!-- NAVBAR -->

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
    <a href="index.php" class="ativo-hover"><img src="img/Icone Casa.png" class="img-nav" alt="Home"></a>
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

  // Cria overlay escuro (se ainda não existir)
  let overlay = document.getElementById("overlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.id = "overlay";
    overlay.style.position = "fixed";
    overlay.style.inset = "0";
    overlay.style.background = "rgba(0, 0, 0, 0.6)";
    overlay.style.zIndex = "9";
    overlay.style.display = "none";
    overlay.style.opacity = "0";
    overlay.style.transition = "opacity 0.3s ease";
    document.body.appendChild(overlay);
  }

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
    searchBar.style.display = "none";
    overlay.style.opacity = "0";
    setTimeout(() => (overlay.style.display = "none"), 300);
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

  // Fecha se clicar fora
  document.addEventListener("click", (e) => {
    if (
      !searchBar.contains(e.target) &&
      !openSearch.contains(e.target) &&
      searchBar.style.display === "block"
    ) {
      fecharPesquisa();
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
  /* ==============================================================
  FIM SCRIPT NAVBAR
  ==============================================================  */
</script>


</html>