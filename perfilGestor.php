<?php
// =========================================================
// 1. INICIA A SESSÃO, CONEXÃO E OBTÉM DADOS DO RESTAURANTE
// =========================================================
session_start();
require_once "backend/conexao.php";

// A ID do restaurante deve estar na sessão para o gestor
if (empty($_SESSION['restaurante_id'])) {
    // Redireciona se o gestor não estiver logado
    header("Location: loginRestaurante.php");
    exit;
}

$restaurante_id = $_SESSION['restaurante_id'];
$restaurante_data = [];

// 1.2. Busca os dados completos do restaurante, incluindo a logo
$sql = "SELECT 
   nome_restaurante,
   email_restaurante,
   telefone,
   horario_abertura,
   horario_fechamento,
   descricao,
   logo_res,
   cep_res,
   endereco_rua_res,
   endereco_num_res,
   endereco_bairro_res,
   endereco_cidade_res,
   endereco_estado_res
  FROM restaurantes 
  WHERE idrestaurante = :id";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $restaurante_id]);
    $restaurante_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$restaurante_data) {
        // Se o ID da sessão não corresponder a nenhum restaurante, desloga e redireciona
        session_destroy();
        header("Location: loginRestaurante.php?erro=restaurante_nao_encontrado");
        exit;
    }
} catch (PDOException $e) {
    // Registra o erro detalhado e exibe uma mensagem genérica
    error_log("Erro ao buscar dados do restaurante: " . $e->getMessage());
    die("Erro interno ao carregar perfil. Por favor, tente novamente.");
}

// 1.3. Define as variáveis para uso no HTML (com valores padrão e sanitização)
$nome = htmlspecialchars($restaurante_data['nome_restaurante'] ?? 'Nome do Restaurante');
$email = htmlspecialchars($restaurante_data['email_restaurante'] ?? 'email@restaurante.com');
$telefone = htmlspecialchars($restaurante_data['telefone'] ?? '(00) 0000-0000');
$abertura = (new DateTime($restaurante_data['horario_abertura'] ?? '00:00:00'))->format('H:i');
$fechamento = (new DateTime($restaurante_data['horario_fechamento'] ?? '00:00:00'))->format('H:i');
$descricao = htmlspecialchars($restaurante_data['descricao'] ?? 'Sem descrição.');

$cep = htmlspecialchars($restaurante_data['cep_res'] ?? '00000-000');
$rua = htmlspecialchars($restaurante_data['endereco_rua_res'] ?? 'Rua de Exemplo');
$numero = htmlspecialchars($restaurante_data['endereco_num_res'] ?? 'N/A');
$bairro = htmlspecialchars($restaurante_data['endereco_bairro_res'] ?? 'Bairro');
$cidade = htmlspecialchars($restaurante_data['endereco_cidade_res'] ?? 'Cidade');
$estado = htmlspecialchars($restaurante_data['endereco_estado_res'] ?? 'UF');


// =========================================================
// 1.4. LÓGICA DA LOGO DO RESTAURANTE
// =========================================================
$has_logo = !empty($restaurante_data['logo_res']);
$logo_base64 = '';

// Se houver dados na coluna logo_res, converte para Base64 para exibir no HTML
if ($has_logo) {
    // Assumindo que a imagem é um JPEG. Se você salvar outros tipos, deve salvar o MIME type
    $logo_base64 = 'data:image/jpeg;base64,' . base64_encode($restaurante_data['logo_res']);
}

// Define o SRC final da imagem
$src_logo = $has_logo
    ? $logo_base64
    // Se não houver logo, usa o placeholder com a primeira letra do nome
    : 'https://placehold.co/120x120/d76a03/ffffff?text=' . strtoupper(substr($nome, 0, 1));

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/Logo.png">
    <link rel="stylesheet" href="src/css/padrão.css">
    <link rel="stylesheet" href="src/css/navbar.css">
    <link rel="stylesheet" href="src/css/perfilGestor.css">
    <title>Perfil - ReservAI</title>

</head>

<body>
    <div class="barra-topo">

        <div class="esquerda">
            <img src="img/Icone Voltar.png" alt="Voltar" id="voltar" class="icone-voltar">
            <h1>Perfil</h1>
        </div>

        <div class="direita">
            <a href="editarPerfilGestor.php">
                <img src="img/Lapis.png" alt="Editar" class="icone-editar">
            </a>
        </div>

    </div>

    <main class="perfil-container">

        <div class="perfil-header">
            <div class="profile-picture-wrapper">

                <img src="<?php echo $src_logo; ?>" alt="Logo do Restaurante" class="profile-picture">

                <a href="editarPerfilGestor.php" class="camera-icon">
                    <img src="img/Icone Camera.png" alt="Trocar Logo">
                </a>
            </div>
            <h2 class="nome-cliente"><?php echo $nome; ?></h2>

        </div>

        <section class="info-section">
            <h3>Informações de Contato e Horário</h3>

            <div class="info-card">
                <div class="info-item">
                    <span class="label">E-mail</span>
                    <p class="valor"><?php echo $email; ?></p>
                </div>

                <div class="info-item">
                    <span class="label">Telefone</span>
                    <p class="valor"><?php echo $telefone; ?></p>
                </div>

                <div class="info-item">
                    <span class="label">Horário de Funcionamento</span>
                    <p class="valor"><?php echo $abertura . " às " . $fechamento; ?></p>
                </div>
            </div>
        </section>

        <section class="info-section">
            <h3>Endereço</h3>

            <div class="info-card">
                <div class="info-item">
                    <span class="label">Rua, Número e Bairro</span>
                    <p class="valor"><?php echo $rua . ", " . $numero . " - " . $bairro; ?></p>
                </div>

                <div class="info-item">
                    <span class="label">CEP</span>
                    <p class="valor"><?php echo $cep; ?></p>
                </div>

                <div class="info-item">
                    <span class="label">Cidade / Estado</span>
                    <p class="valor"><?php echo $cidade . " - " . $estado; ?></p>
                </div>
            </div>
        </section>

        <section class="info-section">
            <h3>Descrição do Restaurante</h3>
            <p class="descricao-texto"><?php echo $descricao; ?></p>
        </section>

        <a href="backend/logoutGestor.php" class="btn-sair">Sair da Conta do Restaurante</a>

    </main>


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


    <br><br><br>
    <br><br><br>

</body>
<script>
    /*============================================================== 
  BOTÃO VOLTAR
  ==============================================================*/
    document.getElementById('voltar').addEventListener('click', function() {
        history.back();
    });

</script>

</html>