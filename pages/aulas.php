<?php

session_start();

require_once '../config/connDB.php';

$assinante = false;

$sql = "SELECT * FROM assinaturas
        WHERE id_usuario = :id_usuario
        ORDER BY data_inicio
        LIMIT 1";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_usuario' => $_SESSION['id_usuario']
]);

$assinatura = $stmt->fetch(PDO::FETCH_ASSOC);

if ($assinatura != null){
    if ($assinatura["status"] == "ativa"){
        $assinante = true;
    }
}



?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aulas</title>
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

    <section class="aulas-gratis">

        <div class="title-aulas">
            <h1>Aulas gratuitas</h1>
            <p>Aprenda sobre investimentos e educação financeira com nossas aulas gratuitas.</p>
        </div>

        <div class="aulas-scroll">
            <iframe src="https://www.youtube.com/embed/videoseries?list=PL8dPuuaLjXtOfse2ncvffeelTrqv9k8lb" title="Aulas gratuitas de investimentos e educação financeira" frameborder="0" allowfullscreen></iframe>

            <iframe src="https://www.youtube.com/embed/videoseries?list=PL8dPuuaLjXtOfse2ncvffeelTrqv9k8lb" title="Aulas gratuitas de investimentos e educação financeira" frameborder="0" allowfullscreen></iframe>
            
            <iframe src="https://www.youtube.com/embed/videoseries?list=PL8dPuuaLjXtOfse2ncvffeelTrqv9k8lb" title="Aulas gratuitas de investimentos e educação financeira" frameborder="0" allowfullscreen></iframe>
        </div>

    </section>
    
    <?php if (!$assinante): ?>

        <div class="locked">
            <i class="ph ph-lock-key"></i>
            <button class="hero-btn-primary" onclick="window.location.href = 'assinatura.php'">Fazer sua assinatura!</button>
        </div>

    <?php endif; ?>
    <section class="aulas-pagas">
        <div class="title-aulas">
            <h1>Aulas pagas</h1>
            <p>Aprenda sobre investimentos e educação financeira com nossas aulas pagas.</p>
        </div>
        
        <div class="<?= !$assinante ? "blur-locked" : ""; ?>">
            <div>          
                <h2>Seção 1</h2>

                <div class="aulas-scroll">
                    <iframe src="https://www.youtube.com/embed/RtecnEnuX_I" title="Aulas pagas de investimentos e educação financeira" frameborder="0" allowfullscreen style="<?= !$assinante ? "pointer-events: none;" : ""; ?>"></iframe>
                </div>
            </div>

            <div>
                <h2>Seção 2</h2>

                <div class="aulas-scroll">
                    <iframe src="https://www.youtube.com/embed/videoseries?list=PL8dPuuaLjXtOfse2ncvffeelTrqv9k8lb" title="Aulas pagas de investimentos e educação financeira" frameborder="0" allowfullscreen style="<?= !$assinante ? "pointer-events: none;" : ""; ?>"></iframe>
                </div>
            </div>

            <div>
                <h2>Seção 3</h2>

                <div class="aulas-scroll">
                    <iframe src="https://www.youtube.com/embed/videoseries?list=PL8dPuuaLjXtOfse2ncvffeelTrqv9k8lb" title="Aulas pagas de investimentos e educação financeira" frameborder="0" allowfullscreenv style="<?= !$assinante ? "pointer-events: none;" : ""; ?>"></iframe>
                </div>
            </div>
        </div>
        
    </section>

    <?php require_once "../includes/footer.php" ?>
</body>
</html>