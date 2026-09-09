<?php

session_start();

require_once "../config/connDB.php";


// Ações
$url = "https://brapi.dev/api/quote/list?type=stock&limit=20";

$response = file_get_contents($url);

$data = json_decode($response, true);

$acoes = $data["stocks"];

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mercado</title>
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
    
    <div class="options-market">
        <button onclick="">Ações</button>
    </div>

    <section style="padding: 10px 20px;" class="sec-acao hidden">
        <?php foreach ($acoes as $acao): ?>

            <div class="card-acao">

                <h2><?= htmlspecialchars($acao["stock"]) ?></h2>

                <p><?= htmlspecialchars($acao["name"]) ?></p>

                <strong>
                    R$ <?= number_format($acao["close"], 2, ',', '.') ?>
                </strong>

                <span>
                    <?= number_format($acao["change"], 2, ',', '.') ?>%
                </span>

            </div>

        <?php endforeach; ?>
    </section>

    <?php require_once "../includes/footer.php" ?>
</body>

<script>

const sec_acao = document.querySelector(".sec-acao");


</script>

</html>