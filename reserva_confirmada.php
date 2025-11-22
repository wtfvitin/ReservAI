<?php
$sucesso = isset($_GET["sucesso"]) && $_GET["sucesso"] == 1;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
       
    <meta charset="UTF-8">
       
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reserva Confirmada - ReservAI</title>
       
    <link rel="shortcut icon" type="image/x-icon" href="img/Logo.png">
    <link rel="stylesheet" href="src/css/padrão.css">
       
    <link rel="stylesheet" href="src/css/navbar.css">
       
    <link rel="stylesheet" href="src/css/reserva_confirmada.css">
</head>

<body>

        <div class="container">
                <?php if ($sucesso): ?>
                        <h1>Reserva Confirmada!</h1>
                        <p>Sua reserva foi criada com sucesso no sistema.</p>
                    <?php else: ?>
                        <h1>Erro</h1>
                        <p>Não foi possível confirmar sua reserva.</p>
                    <?php endif; ?>

                <img src="img/cozinheiro feliz.png" alt="Cozinheiro Feliz">

                <div class="button-group">
                        <a class="btn" href="agenda.php">Ver Agenda</a>
                        <a class="btn" href="index.php" style="background-color: #bf3100;">Voltar ao Início</a>
                    </div>
            </div>

</body>
</html>