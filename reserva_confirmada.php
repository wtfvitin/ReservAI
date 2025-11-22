<?php
$sucesso = isset($_GET["sucesso"]) && $_GET["sucesso"] == 1;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Reserva Confirmada - ReservAI</title>
    <link rel="stylesheet" href="src/css/padrão.css">
    <link rel="stylesheet" href="src/css/navbar.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
            padding: 20px;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 20px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.09);
        }

        h1 {
            color: #e0a606ff;
            margin-bottom: 10px;
        }

        p {
            font-size: 18px;
            margin-bottom: 25px;
        }

        .btn {
            background-color: #f39c12;
            color: white;
            padding: 12px 22px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 10px;
        }

        .btn:hover {
            background-color: #d9830f;
        }
    </style>
</head>

<body>

    <div class="container">
        <?php if ($sucesso): ?>
            <h1> Reserva Confirmada!</h1>
            <p>Sua reserva foi criada com sucesso no sistema.</p>
        <?php else: ?>
            <h1>Erro</h1>
            <p>Não foi possível confirmar sua reserva.</p>
        <?php endif; ?>

        <a class="btn" href="agenda.html">Ver Agenda</a>
        <br>
        <a class="btn" href="index.php">Voltar ao Início</a>
    </div>

</body>

</html>