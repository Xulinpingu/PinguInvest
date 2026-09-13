<?php

session_start();

require_once '../config/connDB.php';

$assinante = false;
$admin = false;

if ($_SESSION['email'] == "xulinpingu.equipe@gmail.com") {
    $admin = true;
}

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
    if ($assinatura["status"] == "ativa") {
        $assinante = true;
    }
}

$sql = "SELECT * FROM aulas
        WHERE gratis = :gratis";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':gratis' => 1
]);

$aulas_gratis = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt->execute([
    ':gratis' => 0
]);

$aulas_pagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM aulas";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$aulas_totais = $stmt->fetchAll(PDO::FETCH_ASSOC);

$num_aulasTotais = count($aulas_totais);

$sql = "SELECT * FROM secoes";
$stmt = $pdo->prepare($sql);

$stmt->execute();

$secoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT id_aula FROM aula_secao
        WHERE id_secao = :id_secao";
$stmt_aulasecao = $pdo->prepare($sql);

$sql = "SELECT * FROM aulas
        WHERE id_aula = :id_aula";
$stmt_aula = $pdo->prepare($sql);

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

            <?php if ($admin): ?>

                <button class="add-aula">
                    <i class="ph ph-plus"></i>
                    <p style="">Adicionar aula</p>
                </button>

                <form class="confirm-overlay hidden" method="POST" action="../actions/add_aula.php">
                    <div class="confirm-popup">

                        <h3 id="confirm-title">Adicionar aula grátis</h3>

                        <input name="video-name" class="confirm-details" id="video-name" style="width: 100%; font-size: 20px;" type="text" placeholder="Nome da aula" required>

                        <input name="video-link" class="confirm-details" id="video-link" style="width: 100%; font-size: 20px;" type="text" placeholder="Cole o link aqui" required>

                        <input type="hidden" name="gratis" value="1">

                        <div class="confirm-buttons">
                            <button type="button" class="cancel-btn" id="confirm-no">
                                Cancelar
                            </button>

                            <button type="submit" class="confirm-btn" id="confirm-yes">
                                Adicionar
                            </button>
                        </div>

                    </div>
                </form>

            <?php endif; ?>

            <?php foreach ($aulas_gratis as $aula): ?>
                <div class="aula">
                    <?php if($admin): ?>

                        <form action="../actions/delete_aula.php" method="POST">
                            <button class="remove-aula" type="submit"><i class="ph ph-minus"></i></button>
                            <input type="hidden" name="id-aula" value="<?=$aula['id_aula']?>">
                        </form>

                    <?php endif; ?>

                    <iframe 
                        src="<?= $aula['link'] ?>" 
                        title="<?= $aula['nome'] ?>" 
                        frameborder="0" 
                        allowfullscreen>
                    </iframe>
            </div>
            <?php endforeach; ?>
        </div>

    </section>
    
    <?php if (!$assinante && !$admin): ?>

        <div class="locked">
            <i class="ph ph-lock-key"></i>
            <button class="hero-btn-primary" onclick="window.location.href = 'assinatura.php'">Fazer sua assinatura!</button>
        </div>

    <?php endif; ?>
    <section class="aulas-pagas">
        <div class="title-aulas">
            <div style="display: flex; align-items: center; gap: 10px;">
                <h1>Aulas pagas</h1>

                <?php if ($admin): ?>

                    <button class="hero-btn-primary add-secao" style="border-radius: 100px; padding: 5px 10px;"> <i class="ph ph-plus" style="font-size: 20px;"></i> Adicionar seção </button>

                    <form class="confirm-overlay secao-overlay hidden" method="POST" action="../actions/add_secao.php">
                        <div class="confirm-popup">

                            <h3 id="confirm-title">Adicionar seção</h3>

                            <input name="secao-name" class="confirm-details" id="secao-name" style="width: 100%; font-size: 20px;" type="text" placeholder="Nome da seção" required>

                            <div class="confirm-buttons">
                                <button type="button" class="cancel-btn" id="confirm-no">
                                    Cancelar
                                </button>

                                <button type="submit" class="confirm-btn" id="confirm-yes">
                                    Adicionar
                                </button>
                            </div>

                        </div>
                    </form>

                <?php endif; ?>
            </div>
            <p>Aprenda sobre investimentos e educação financeira com nossas aulas pagas.</p>
        </div>
        
        <div class="<?= !$assinante && !$admin ? "blur-locked" : ""; ?>">

            <div>          
                <div class="aulas-scroll" style="<?= !$assinante && !$admin ? "overflow-x: hidden !important;" : "" ?>">

                    <?php if ($admin): ?>

                        <button class="add-aula">
                            <i class="ph ph-plus"></i>
                            <p style="">Adicionar aula</p>
                        </button>

                        <form class="confirm-overlay hidden" method="POST" action="../actions/add_aula.php">
                            <div class="confirm-popup">

                                <h3 id="confirm-title">Adicionar aula paga</h3>

                                <input name="video-name" class="confirm-details" id="video-name" style="width: 100%; font-size: 20px;" type="text" placeholder="Nome da aula" required>

                                <input name="video-link" class="confirm-details" id="video-link" style="width: 100%; font-size: 20px;" type="text" placeholder="Cole o link aqui" required>

                                <input type="hidden" name="gratis" value="0">

                                <div class="confirm-buttons">
                                    <button type="button" class="cancel-btn" id="confirm-no">
                                        Cancelar
                                    </button>

                                    <button type="submit" class="confirm-btn" id="confirm-yes">
                                        Adicionar
                                    </button>
                                </div>

                            </div>
                        </form>

                    <?php endif; ?>

                    <?php foreach ($aulas_pagas as $aula): ?>
                        <div class="aula">
                            <?php if($admin): ?>

                                <form action="../actions/delete_aula.php" method="POST">
                                    <button class="remove-aula" type="submit"><i class="ph ph-minus"></i></button>
                                    <input type="hidden" name="id-aula" value="<?=$aula['id_aula']?>">
                                </form>

                            <?php endif; ?>

                            <iframe 
                                class="aula-video"
                                src="<?= $aula['link'] ?>" 
                                title="<?= $aula['nome'] ?>" 
                                frameborder="0" 
                                allowfullscreen 
                                style="<?= !$assinante && !$admin ? "pointer-events: none;" : "" ?>">
                            </iframe>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>

            <?php foreach ($secoes as $secao): ?>

                <?php
                    $stmt_aulasecao->execute([
                        ':id_secao' => $secao['id_secao']
                    ]);

                    $aulas_id = $stmt_aulasecao->fetchAll(PDO::FETCH_COLUMN);

                    $num_aulasSecao = count($aulas_id);

                    if($num_aulasSecao == 0 & !$admin){
                        break;
                    }
                ?>

                <div>          
                    <h2><?= $secao['nome'] ?></h2>

                    <div class="aulas-scroll" style="<?= !$assinante && !$admin ? "overflow-x: hidden !important;" : "" ?>">

                        <?php if ($admin & $num_aulasSecao < $num_aulasTotais): ?>

                            <button class="add-aula">
                                <i class="ph ph-plus"></i>
                                <p style="">Adicionar aula</p>                             
                            </button>

                            <form class="confirm-overlay hidden" method="POST" action="../actions/add_aulasecao.php">
                                <div class="confirm-popup">

                                    <h3 id="confirm-title">Adicionar aula paga à seção <?= $secao['nome'] ?></h3>

                                    <?php foreach ($aulas_totais as $aula): ?>

                                        <?php if(!in_array($aula['id_aula'], $aulas_id)): ?>

                                            <label class="checkbox-personalizado"> 
                                                <input 
                                                    type="checkbox" 
                                                    name="aulas[]" 
                                                    value="<?=$aula['id_aula']?>"
                                                >
                                                <span class="checkmark"></span>
                                                <?=$aula['nome']?>
                                            </label>
                                        
                                            <br>

                                        <?php endif; ?>

                                    <?php endforeach; ?>

                                    <input type="hidden" name="id_secao" value="<?= $secao['id_secao'] ?>">

                                    <div class="confirm-buttons">
                                        <button type="button" class="cancel-btn" id="confirm-no">
                                            Cancelar
                                        </button>

                                        <button type="submit" class="confirm-btn" id="confirm-yes">
                                            Adicionar
                                        </button>
                                    </div>

                                </div>
                            </form>

                        <?php endif; ?>

                        <?php foreach ($aulas_id as $aula_id): ?>
                            <?php
                                $stmt_aula->execute([
                                    ':id_aula' => $aula_id
                                ]);

                                $aula = $stmt_aula->fetch(PDO::FETCH_ASSOC);
                            ?>

                            <div class="aula">
                                <?php if($admin): ?>

                                    <form action="../actions/delete_aula.php" method="POST">
                                        <button class="remove-aula" type="submit"><i class="ph ph-minus"></i></button>
                                        <input type="hidden" name="id-aula" value="<?=$aula['id_aula']?>">    
                                        <input type="hidden" name="id-secao" value="<?=$secao['id_secao']?>"> 
                                    </form>

                                <?php endif; ?>
                                
                                                         
                                <iframe 
                                    src="<?= $aula['link'] ?>" 
                                    title="<?= $aula['nome'] ?>" 
                                    frameborder="0" 
                                    allowfullscreen 
                                    style="<?= !$assinante && !$admin ? "pointer-events: none;" : "" ?>">
                                </iframe>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>

            <?php endforeach; ?>

        </div>
        
    </section>

    <?php require_once "../includes/footer.php" ?>
</body>

<script>

const addAula_btns = document.querySelectorAll('.add-aula');
const addSecao_btn = document.querySelector('.add-secao');
const secao_overlay = document.querySelector('.secao-overlay');

const cancel_btns = document.querySelectorAll('.cancel-btn');

addSecao_btn.addEventListener('click', () => {
    secao_overlay.classList.remove('hidden');
});

addAula_btns.forEach(btn => {
    btn.addEventListener('click', () => {
        btn.closest('.aulas-scroll').querySelector('.confirm-overlay').classList.remove('hidden');
    });
});

cancel_btns.forEach(btn => {
    btn.addEventListener('click', () => {
        btn.closest('.confirm-overlay').classList.add('hidden');
    });
});

</script>

</html>