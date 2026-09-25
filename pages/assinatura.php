<?php

session_start();

require_once '../config/connDB.php';
require_once "../actions/check_logado.php";

if (!$logado) {
    header("Location: ../pages/login.php");
    exit();
}
else{
    $idUser = $_SESSION['id_usuario'];
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossos Planos</title>

    <link rel="icon" type="image/png" href="../assets/images/logo/pinguin.png">

    <!-- Aplica o tema salvo (localStorage) antes de renderizar a página, evitando flash do tema errado -->
    <script src="../assets/js/theme.js"></script>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <script defer src="../assets/js/functions.js"></script>
</head>
<body>
    <?php require_once "../includes/header.php" ?>

    <div class="title-aulas" style="margin-top: 28px;">
        <h1>Nossos planos</h1>
        <p>Escolha o plano ideal para acessar todo o conteúdo pago do PinguInvest.</p>
    </div>

    <form class="feature-grid plans-grid" action="../actions/assinar.php" method="POST" style="max-width: 1000px; margin: 0 auto 40px; padding: 0 20px;">

        <div class="feature-card plan-card">
            <h2>MENSAL</h2>
            <p>Nosso menor plano, com duração de apenas 1 mês e menor custo-benefício de todos.</p>

            <p class="plan-price">R$00,00<span>/mês</span></p>
            <label class="hero-btn-primary" for="mes">Fazer plano</label>
            <input type="radio" name="mes" id="mes" class="opcao" style="display: none;">
        </div>

        <div class="feature-card plan-card plan-popular">
            <span class="plan-badge">Mais popular</span>
            <h2>TRIMESTRAL</h2>
            <p>Nosso plano médio, com duração de 3 meses e melhor custo-benefício em comparação ao mensal.</p>

            <p class="plan-price">R$00,00<span>/3 meses</span></p>
            <label class="hero-btn-primary" for="tri">Fazer plano</label>
            <input type="radio" name="tri" id="tri" class="opcao" style="display: none;">
        </div>

        <div class="feature-card plan-card">
            <h2>ANUAL</h2>
            <p>Nosso plano anual e maior, com duração de 1 ano completo e de maior custo-benefício.</p>

            <p class="plan-price">R$00,00<span>/ano</span></p>
            <label class="hero-btn-primary" for="ano">Fazer plano</label>
            <input type="radio" name="ano" id="ano" class="opcao" style="display: none;">
        </div>

    </form>

    <?php require_once "../includes/footer.php" ?>
</body>
<script>

const opcoes = document.querySelectorAll(".opcao");

const form = document.querySelector("form")

opcoes.forEach(radio => {
    radio.addEventListener('change', (event) => {
        form.submit();
    });
});

</script>
</html>