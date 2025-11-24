<?php 
// =================================================================
// PÁGINA INICIAL DO GESTOR DE RESTAURANTE
// Exibe as reservas confirmadas para o dia atual.
// =================================================================

session_start();
// O caminho deve ser ajustado para onde o arquivo de conexão realmente está
require_once "backend/conexao.php"; 

// 1. Verificação de Autenticação
if (empty($_SESSION['restaurante_id'])) {
    header("Location: loginRestaurante.html"); 
    exit;
}

$restaurante_id = $_SESSION['restaurante_id'];
$reservas_do_dia = [];
$hoje = date('Y-m-d'); 
$mensagem_erro = ""; // Inicializa a variável de erro

// 2. Consulta SQL Corrigida (Removendo a referência à tabela 'mesas' e 'mesa_id')
$sql_reservas = "
    SELECT 
        r.idreserva,
        c.nome_cli,
        c.sobrenome_cli,
        c.foto_perfil,
        r.horario_inicio,
        r.horario_fim,
        -- REMOVIDA: r.mesa_id, m.numero AS numero_mesa,
        r.numero_clientes,
        r.status
    FROM 
        reservas r
    JOIN 
        clientes c ON r.cliente_id = c.idcliente
    WHERE 
        r.restaurante_id = :restaurante_id AND 
        r.data_reserva = :hoje AND
        r.status = 'confirmada' 
    ORDER BY 
        r.horario_inicio ASC
";

try {
    $stmt = $pdo->prepare($sql_reservas);
    $stmt->bindParam(':restaurante_id', $restaurante_id, PDO::PARAM_INT);
    $stmt->bindParam(':hoje', $hoje);
    $stmt->execute();
    $reservas_do_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Loga o erro real no servidor para debug
    error_log("Erro ao buscar reservas: " . $e->getMessage()); 
    // Mensagem amigável para o usuário
    $mensagem_erro = "Não foi possível carregar as reservas do dia. Erro de banco de dados."; 
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
    <link rel="stylesheet" href="src/css/reservasDia.css"> 
    
    <title>Reservas do Dia - ReservAI</title>
    
</head>

<body>
    <div class="titulo">
        <div class="titulo-esquerda">
            <h1>Reservas do Dia</h1>
            <img src="img/Icone Check.png" alt="Check" class="icone-check">
        </div>
    </div>
    
    <div class="content">

        <?php if (!empty($mensagem_erro)) : ?>
            <div class="nenhuma-reserva"><?php echo $mensagem_erro; ?></div>
        <?php elseif (empty($reservas_do_dia)) : ?>
            <div class="nenhuma-reserva">
                <h1 style="color: #d76a03; font-size: 30px; text-align: center; margin-top: 100px; margin-bottom: 0px;">Nenhuma reserva confirmada para o dia de hoje.</h1>
                <img class="sem-reserva-img" src="img/cozinheiro.png" alt="Cozinheiro Sugerindo Reserva">
            </div>
        <?php else : ?>
            
            <?php foreach ($reservas_do_dia as $reserva) : 
                
                $nome_completo = $reserva['nome_cli'] . ' ' . $reserva['sobrenome_cli'];
                $inicial_nome = strtoupper(substr($reserva['nome_cli'], 0, 1));
                
                // Como não estamos mais buscando foto_perfil do banco, assumimos que é um placeholder.
                // Contudo, a consulta acima ainda busca a foto, então mantemos a lógica de exibição.
                $has_foto = !empty($reserva['foto_perfil']);

                $foto_src = $has_foto
                    ? 'data:image/jpeg;base64,' . base64_encode($reserva['foto_perfil'])
                    : 'https://placehold.co/120x120/d76a03/ffffff?text=' . $inicial_nome;
            ?>

                <div class="reserva-card" id="reserva-<?php echo $reserva['idreserva']; ?>">
                    <div class="reserva-header">
                        
                        <img src="<?php echo $foto_src; ?>" alt="Foto de <?php echo htmlspecialchars($reserva['nome_cli']); ?>">
                        
                        <strong><?php echo htmlspecialchars($nome_completo); ?></strong>
                    </div>

                    <div class="reserva-info-linha">
                        <span>Horário:</span> 
                        <strong><?php echo (new DateTime($reserva['horario_inicio']))->format('H\hi'); ?> às <?php echo (new DateTime($reserva['horario_fim']))->format('H\hi'); ?></strong>
                    </div>

                    <hr>

                    <div class="reserva-info-linha">
                        <span>Mesa:</span> 
                        <!-- CORRIGIDO: Agora apenas exibe "Não Definida" ou "Em breve" -->
                        <strong>Não Definida (A ser implementado)</strong> 
                    </div>

                    <hr>
                    
                    <div class="reserva-info-linha">
                        <span>Pessoas:</span> 
                        <strong><?php echo htmlspecialchars($reserva['numero_clientes']); ?> Pessoas</strong>
                    </div>
                    
                    <button type="button" class="btn-cancelar" data-reserva-id="<?php echo $reserva['idreserva']; ?>">
                        CANCELAR
                    </button>

                    <form id="form-cancelar-<?php echo $reserva['idreserva']; ?>" method="POST" action="backend/cancelarReservaGestor.php" style="display: none;">
                        <input type="hidden" name="id_reserva" value="<?php echo $reserva['idreserva']; ?>">
                    </form>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <br><br><br>
    
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

    <nav class="navbar">
        <a href="indexRestaurante.php" class="ativo-hover"><img src="img/Icone Casa.png" class="img-nav" alt="Home"></a>

        <a href="configuracoesRestaurante.html" class="desativo-hover"><img src="img/Icone Configurações.png" class="img-nav" alt="Configurações"></a>

        <a href="perfilGestor.php" class="desativo-hover">
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
    const overlay = document.getElementById("overlay"); // O overlay agora é compartilhado
    // const openSearch = document.getElementById("openSearch"); // Variável não existe neste HTML
    // const searchBar = document.getElementById("searchBar"); // Variável não existe neste HTML
    // const searchInput = document.getElementById("searchInput"); // Variável não existe neste HTML

    // ============================================================== 
    // FUNÇÕES DO MODAL DE CANCELAMENTO
    // ============================================================== 
    function abrirModal(reservaId) {
        reservaIdParaCancelar = reservaId;
        modal.classList.add('show');
        overlay.style.display = 'block';
        setTimeout(() => {
            overlay.style.opacity = '1';
        }, 10);
    }

    function fecharModal() {
        modal.classList.remove('show');
        overlay.style.opacity = '0';
        setTimeout(() => {
            // Como a barra de pesquisa e openSearch não existem, simplificamos o fecharModal
            overlay.style.display = 'none';
            reservaIdParaCancelar = null;
        }, 300);
    }

    // 1. Adiciona listener a todos os botões Cancelar
    document.querySelectorAll('.btn-cancelar').forEach(button => {
        button.addEventListener('click', function() {
            // Captura o ID da reserva do atributo data-reserva-id
            const reservaId = this.getAttribute('data-reserva-id');
            if (reservaId) {
                abrirModal(reservaId);
            }
        });
    });

    // 2. Listener para o botão "Não, Manter" no modal
    btnFecharModal.addEventListener('click', fecharModal);

    // 3. Listener para o botão "Sim, Cancelar" no modal
    btnConfirmar.addEventListener('click', function() {
        if (reservaIdParaCancelar) {
            // Encontra o formulário escondido correspondente e o submete
            const form = document.getElementById(`form-cancelar-${reservaIdParaCancelar}`);
            if (form) {
                form.submit();
                // A navegação ocorre após o submit, então fecharModal não é estritamente necessário
                // mas é mantido para garantir que o modal suma se a submissão falhar por algum motivo
                // e o usuário permanecer na página.
                fecharModal(); 
            }
        }
    });
    
    // 4. Fechar o modal clicando no overlay
    overlay.addEventListener("click", (e) => {
        if (modal.classList.contains('show')) {
            fecharModal();
        }
    });

</script>


</html>