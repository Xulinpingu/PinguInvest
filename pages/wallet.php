<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carteira</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/logo/pinguin.png">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <script defer src="../assets/js/functions.js"></script>
</head>
<body>
    <?php require_once "../includes/header.php" ?>

    <main class="wallet-container">

        <section class="wallet-summary">

            <div class="card-circle"></div>

            <span class="wallet-label">PATRIMÔNIO TOTAL</span>

            <h1>R$ 0,00</h1>

            <p class="wallet-profit">▲ 0,00% hoje</p>

            <div class="wallet-stats">  
                <div>
                    <span>INVESTIDO</span>
                    <strong>R$ 0,00</strong>
                </div>

                <div>
                    <span>RETORNO</span>
                    <strong>R$ 0,00</strong>
                </div>

                <div>
                    <span>% TOTAL</span>
                    <strong>0,00%</strong>
                </div>
            </div>
        </section>

        <section class="dashboard-grid">

            <div class="dashboard-card">
                <h3>Evolução</h3>

                <div class="placeholder-chart">
                    Gráfico em breve
                </div>
            </div>

            <div class="dashboard-card">
                <h3>Alocação</h3>

                <div class="placeholder-chart">
                    Pizza em breve
                </div>
            </div>

        </section>

    </main>

    <?php require_once "../includes/footer.php" ?>
</body>
</html>