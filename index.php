<?php session_start(); ?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Início</title>

  <link rel="stylesheet" href="src/css/padrão.css">
  <link rel="stylesheet" href="src/css/navbar.css">
  <link rel="stylesheet" href="src/css/index.css">

</head>

<body>

  <div class="titulo">
    <div class="titulo-esquerda">
      <h1>Reserve Aí!</h1>
      <img src="img/Table.png" alt="Mesa de Restaurante">
    </div>

    <div class="localizacao">
      <img src="img/Icone Localizacao.png" alt="Localização" class="icone-localizacao">
      <span class="texto-localizacao">Mauá, SP</span>
    </div>
  </div>
  <div class="content">
    <div class="card">
      <img src="img/Restaurante.jpg" alt="Restaurante">
      <div class="card-content">
        <h2>Restaurante</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas turpis ipsum, dignissim at tempus eget,
          venenatis vitae mi. Sed id ligula mauris. Phasellus egestas lobortis nisi non consequat.</p>
        <a href="reserva.html"><button>Reserve já</button></a>
      </div>
    </div>
    <div class="card">
      <img src="img/Restaurante.jpg" alt="Restaurante">
      <div class="card-content">
        <h2>Restaurante</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas turpis ipsum, dignissim at tempus eget,
          venenatis vitae mi. Sed id ligula mauris. Phasellus egestas lobortis nisi non consequat.</p>
        <a href="reserva.html"><button>Reserve já</button></a>
      </div>
    </div>
    <div class="card">
      <img src="img/Restaurante.jpg" alt="Restaurante">
      <div class="card-content">
        <h2>Restaurante</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas turpis ipsum, dignissim at tempus eget,
          venenatis vitae mi. Sed id ligula mauris. Phasellus egestas lobortis nisi non consequat.</p>
        <a href="reserva.html"><button>Reserve já</button></a>
      </div>
    </div>
    <div class="card">
      <img src="img/Restaurante.jpg" alt="Restaurante">
      <div class="card-content">
        <h2>Restaurante</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas turpis ipsum, dignissim at tempus eget,
          venenatis vitae mi. Sed id ligula mauris. Phasellus egestas lobortis nisi non consequat.</p>
        <a href="reserva.html"><button>Reserve já</button></a>
      </div>
    </div>
    <div class="card">
      <img src="img/Restaurante.jpg" alt="Restaurante">
      <div class="card-content">
        <h2>Restaurante</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas turpis ipsum, dignissim at tempus eget,
          venenatis vitae mi. Sed id ligula mauris. Phasellus egestas lobortis nisi non consequat.</p>
        <a href="reserva.html"><button>Reserve já</button></a>
      </div>
    </div>
    <div class="card">
      <img src="img/Restaurante.jpg" alt="Restaurante">
      <div class="card-content">
        <h2>Restaurante</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas turpis ipsum, dignissim at tempus eget,
          venenatis vitae mi. Sed id ligula mauris. Phasellus egestas lobortis nisi non consequat.</p>
        <a href="reserva.html"><button>Reserve já</button></a>
      </div>
    </div>
    <div class="card">
      <img src="img/Restaurante.jpg" alt="Restaurante">
      <div class="card-content">
        <h2>Restaurante</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas turpis ipsum, dignissim at tempus eget,
          venenatis vitae mi. Sed id ligula mauris. Phasellus egestas lobortis nisi non consequat.</p>
        <a href="reserva.html"><button>Reserve já</button></a>
      </div>
    </div>
    <div class="card">
      <img src="img/Restaurante.jpg" alt="Restaurante">
      <div class="card-content">
        <h2>Restaurante</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas turpis ipsum, dignissim at tempus eget,
          venenatis vitae mi. Sed id ligula mauris. Phasellus egestas lobortis nisi non consequat.</p>
        <a href="reserva.html"><button>Reserve já</button></a>
      </div>
    </div>
    <div class="card">
      <img src="img/Restaurante.jpg" alt="Restaurante">
      <div class="card-content">
        <h2>Restaurante</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas turpis ipsum, dignissim at tempus eget,
          venenatis vitae mi. Sed id ligula mauris. Phasellus egestas lobortis nisi non consequat.</p>
        <a href="reserva.html"><button>Reserve já</button></a>
      </div>
    </div>
  </div>

  <!-- ==============================================================
    NAVBAR COMPLETA
    ==============================================================  -->

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
    <a href="index.php" class="ativo-hover"><img src="img/Icone Casa.png" class="img-nav" alt="Home"></a>
    <a href="#" class="desativo-hover"><img src="img/Icone Agenda.png" class="img-nav" alt="Agenda"></a>

    <a href="#" class="search-btn" id="openSearch">
      <img src="img/Icone Lupa.png" class="img-lupa-nav" alt="Pesquisar">
      <img src="img/Icone X.png" class="close-icon" alt="Fechar">
    </a>

    <a href="#" class="desativo-hover"><img src="img/Icone Configurações.png" class="img-nav" alt="Configurações"></a>

    <!-- Botão de perfil com redirecionamento dinâmico -->
    <a href="<?php echo isset($_SESSION['usuario_id']) ? 'perfil.php' : 'gestor-cliente.html'; ?>" class="desativo-hover">
      <img src="img/Icone Perfil.png" class="img-nav" alt="Perfil">
      </a>

  </nav>


  <!-- ==============================================================
    FIM NAVBAR
    ==============================================================  -->

</body>
<script>
  /*  ==============================================================
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
  /*  ==============================================================
  FIM SCRIPT NAVBAR
  ==============================================================  */
</script>


</html>