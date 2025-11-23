<!DOCTYPE html>
<html lang="pt-br">

<head>  
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="img/Logo.png"> 
    <link rel="stylesheet" href="src/css/padrão.css">     
    <link rel="stylesheet" href="src/css/personalizarRestaurantePt2.css">
    <title>Cadastro - ReservAI</title>
</head>

<body>
    <a href="cadastroClientePt1.html" class="btnCliente">SOU CLIENTE</a>

    <div class="container">
        <div class="formulario">
            <div class="logo">
                <img src="img/Logo.png" class="logo" alt="Logo do ReservAI">
                <h1>Criar Cardápio</h1>
            </div>

            <h2>Última Etapa: Cardápio</h2>

            <div class="progress-container">
                <div class="progress-step complete">
                    <span class="circle active">1</span>
                    <span class="label active">Acesso</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-line active" style="width: 100%;"></div>
                </div>

                <div class="progress-step complete">
                    <span class="circle active">2</span>
                    <span class="label active">Informações</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-line active" style="width: 100%;"></div>
                </div>

                <div class="progress-step complete">
                    <span class="circle active">3</span>
                    <span class="label active">Endereço</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-line active" style="width: 100%;"></div>
                </div>

                <div class="progress-step complete">
                    <span class="circle active">4</span>
                    <span class="label active">Mídia</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-line active" style="width: 100%;"></div>
                </div>

                <div class="progress-step current">
                    <span class="circle active">5</span>
                    <span class="label active">Cardápio</span>
                </div>
            </div>
                        <form id="form-cardapio" action="backend/processamento_cardapio.php" method="POST" enctype="multipart/form-data">

                <p class="instrucao">Defina a quantidade de pratos e preencha os campos.</p>

                <input type="number" id="total-pratos" name="total_pratos" min="1" max="20"
                    placeholder="Quantos pratos haverá no seu cardápio? (máx. 20)" required
                    class="input-preco">

                <div id="campos-cardapio">
                </div>
                <div style="height: 10px;"></div>
            </form>
        </div>
               
        <div class="fundo">
            <button type="submit" form="form-cardapio" class="btnCadastrar">CONCLUIR</button>
        </div>
    </div>

</body>

<script>
    const inputTotalPratos = document.getElementById('total-pratos');
    const containerCampos = document.getElementById('campos-cardapio');

    /**
     * Função auxiliar para configurar o listener de upload para um input específico.
     * @param {number} indice O número do prato.
     */
    function configurarListenerUpload(indice) {
        const inputArquivo = document.getElementById(`arquivo-upload-${indice}`);
        const nomeArquivoSpan = document.getElementById(`nome-arquivo-${indice}`);
        const textoPadrao = `Comida ${indice}`;
        const placeholderElement = inputArquivo.closest('.prato-campo').querySelector('.placeholder-text');

        if (inputArquivo && nomeArquivoSpan) {
            inputArquivo.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    nomeArquivoSpan.textContent = this.files[0].name;
                    placeholderElement.style.display = 'none';
                } else {
                    nomeArquivoSpan.textContent = '';
                    placeholderElement.style.display = 'block';
                }
            });
        }
    }

    // Função para gerar o HTML de um único par (Imagem + Nome + Preço)
    function gerarCampoPrato(indice) {
        // Usa o novo container 'prato-campo' para agrupar visualmente os campos de um único prato.
        return `
            <div class="prato-campo"> 
                <div class="custom-file-upload">
                    <span class="placeholder-text">Comida ${indice}</span>
                    <label for="arquivo-upload-${indice}" id="label-botao-${indice}">
                        Escolher Imagem
                    </label>
                    <input type="file" id="arquivo-upload-${indice}" 
                    name="imagem_comida_${indice}" accept="image/*" required>
                    <span id="nome-arquivo-${indice}"></span>
                </div>

                <input type="text" class="input-prato"
                name="nome_prato_${indice}"
                placeholder="Nome da Comida ${indice}" required>

                <input type="text" class="input-preco" 
                name="preco_comida_${indice}" 
                placeholder="Preço (Ex: 35.90)" required
                pattern="[0-9]+([,\.][0-9]{1,2})?">
            </div>
        `;
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
            containerCampos.innerHTML = '';
            return;
        }

        if (total > 0) {
            for (let i = 1; i <= total; i++) {
                htmlFinal += gerarCampoPrato(i);
            }
        }

        // Insere o HTML gerado no container
        containerCampos.innerHTML = htmlFinal;

        // Re-aplica o listener de "change" (mostrar nome do arquivo) para cada novo input
        for (let i = 1; i <= total; i++) {
            configurarListenerUpload(i);
        }
    }

    // Adiciona o listener para o evento 'input' (dispara a cada tecla digitada)
    inputTotalPratos.addEventListener('input', gerarCamposCardapio);

    // Gera campos na primeira carga
    document.addEventListener('DOMContentLoaded', gerarCamposCardapio);
</script>

</html>