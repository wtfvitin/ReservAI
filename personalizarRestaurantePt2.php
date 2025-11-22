<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/css/padrão.css">
    <link rel="stylesheet" href="src/css/personalizarRestaurantePt2.css">
    <title>Cadastro - ReservAI</title>
</head>

<body>
    <div class="container">
        <div class="formulario">
            <div class="logo">
                <img src="img/Logo.png" class="logo" alt="Logo do ReservAI">
                <h1>Criar Cardápio</h1>
            </div>

            <form action="backend/processamento_cardapio.php" method="POST" enctype="multipart/form-data">

                <input type="number" id="total-pratos" name="total_pratos" min="1" max="20"
                    placeholder="Quantos pratos haverá no seu cardápio?" required
                    class="input-preco">
                <div id="campos-cardapio">
                </div>
        </div>
        <div class="fundo">
            <button type="submit" class="btnCadastrar">CONCLUIR</button>
        </div>
        </form>
    </div>

</body>

</body>
<script>
    const inputTotalPratos = document.getElementById('total-pratos');
    const containerCampos = document.getElementById('campos-cardapio');

    // Função para gerar o HTML de um único par (Imagem + Preço)
    function gerarCampoPrato(indice) {
        // Gera o HTML da Div do upload de arquivo
        const htmlUpload = `
        <div class="custom-file-upload">
            <label for="arquivo-upload-${indice}" id="label-botao-${indice}">
            Escolher Imagem
            </label>
            <input type="file" id="arquivo-upload-${indice}" 
            name="imagem_comida_${indice}" accept="image/*" required>
                <span id="nome-arquivo-${indice}">Comida ${indice}</span>
        </div>

        <input type="text" class="input-preco"
        name="nome_prato_${indice}"
        placeholder="Nome da Comida ${indice}" required>

        <input type="text" class="input-preco" 
        name="preco_comida_${indice}" 
        placeholder="Preço da Comida ${indice} (Ex: 35.90)" required
        pattern="[0-9]+([,\.][0-9]{1,2})?">
        `;
        return htmlUpload;
    }

    // Função principal que gera todos os campos dinamicamente
    function gerarCamposCardapio() {
        const total = parseInt(inputTotalPratos.value) || 0;
        let htmlFinal = '';

        // Limpa qualquer erro de entrada (limite)
        inputTotalPratos.setCustomValidity('');

        // Limita o número de pratos para evitar sobrecarga (você pode ajustar este valor)
        if (total > 20) {
            inputTotalPratos.setCustomValidity('O número máximo de pratos é 20.');
            inputTotalPratos.reportValidity();
            return;
        }

        if (total > 0) {
            for (let i = 1; i <= total; i++) {
                htmlFinal += gerarCampoPrato(i);
            }
        }

        // Insere o HTML gerado no container
        containerCampos.innerHTML = htmlFinal;

        // Após inserir o HTML, precisamos re-aplicar o listener de "change" (mostrar nome do arquivo)
        for (let i = 1; i <= total; i++) {
            const inputArquivo = document.getElementById(`arquivo-upload-${i}`);
            const nomeArquivoSpan = document.getElementById(`nome-arquivo-${i}`);
            const textoPadrao = `Comida ${i}`;

            if (inputArquivo && nomeArquivoSpan) {
                // Aplica a funcionalidade de mostrar o nome do arquivo
                inputArquivo.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        nomeArquivoSpan.textContent = this.files[0].name;
                    } else {
                        nomeArquivoSpan.textContent = textoPadrao;
                    }
                });
            }
        }
    }

    // Adiciona o listener para o evento 'input' (dispara a cada tecla digitada)
    inputTotalPratos.addEventListener('input', gerarCamposCardapio);

    // Gera campos na primeira carga, caso o navegador preencha o campo automaticamente
    document.addEventListener('DOMContentLoaded', gerarCamposCardapio);
</script>

</html>