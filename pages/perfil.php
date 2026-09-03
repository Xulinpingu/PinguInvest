<?php

session_start();

require_once "../config/connDB.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.php");
    exit();
}

$idUser = $_SESSION['id_usuario'];

$sql = "
    SELECT *
    FROM usuarios
    WHERE id_usuario = :id_usuario
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_usuario' => $idUser
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);


$dataCriacao = new DateTime($usuario['criado_em']);
$dataAtual = new DateTime();

$intervalo = $dataCriacao->diff($dataAtual);


/* Texto do tempo */

if ($intervalo->y > 0) {

    $tempoMembro =
        $intervalo->y .
        ($intervalo->y == 1 ? " ano" : " anos");

} elseif ($intervalo->m > 0) {

    $tempoMembro =
        $intervalo->m .
        ($intervalo->m == 1 ? " mês" : " meses");

} elseif ($intervalo->d > 0) {

    $tempoMembro =
        $intervalo->d .
        ($intervalo->d == 1 ? " dia" : " dias");

} else {

    $tempoMembro = "Hoje";
}


/* Data formatada */

$dataMembro = $dataCriacao->format('d/m/Y');

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil</title>
    <link rel="icon" type="image/png"href="../assets/images/logo/pinguin.png">
    <script src="../assets/js/theme.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <script defer src="../assets/js/functions.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
</head>

<body>

<?php require_once "../includes/header.php"; ?>


<main class="perfil-page">

    <section class="perfil-card">

        <div class="perfil-header">

            <div class="perfil-avatar">

                <img
                    src="<?= !empty($usuario['foto'])
                        ? htmlspecialchars($usuario['foto'])
                        : '../assets/images/profile/default.png' ?>"
                    alt="Foto de perfil"
                >

                <button
                    type="button"
                    class="avatar-edit"
                    onclick="togglePicForm()"
                    aria-label="Editar foto"
                >
                    <i class="ph ph-pencil-simple"></i>
                </button>


                <!-- MENU DA FOTO -->

                <div class="perfil-pic-opts hidden-pic">

                    <form
                        action="../actions/change_icon.php"
                        method="POST"
                        enctype="multipart/form-data"
                        class="upload-pic"
                    >

                        <label for="foto">

                            <i class="ph ph-upload-simple"></i>

                            Alterar foto

                        </label>

                        <input
                            type="file"
                            name="foto"
                            id="foto"
                            accept="image/*"
                        >

                    </form>


                    <?php if (!empty($usuario['foto'])): ?>

                        <form
                            action="../actions/change_icon.php"
                            method="POST"
                        >

                            <button type="submit">

                                <i class="ph ph-trash"></i>

                                Remover foto

                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            </div>


            <div class="perfil-user-info">

                <h1>
                    <?= htmlspecialchars($usuario['nome']) ?>
                </h1>

                <p>
                    <?= htmlspecialchars($usuario['email']) ?>
                </p>

            </div>


            <!-- TEMPO COMO MEMBRO -->

            <div class="member-time">

                <div class="member-icon">
                    <i class="ph ph-calendar-blank"></i>
                </div>

                <div>

                    <span>
                        Membro desde <?= $dataMembro ?>
                    </span>

                    <strong>
                        Está com a gente há <?= $tempoMembro ?>
                    </strong>

                </div>

            </div>

        </div>


        <div class="perfil-divider"></div>

        <div class="subscription-section">

            <div class="section-title">

                <span class="section-title-icon">
                    <i class="ph ph-graduation-cap"></i>
                </span>

                <div>
                    <h2>Aulas de Investimento</h2>

                    <p>
                        Aprenda sobre investimentos.
                    </p>
                </div>

            </div>


            <div class="subscription-placeholder">

                <div class="subscription-placeholder-icon">
                    <i class="ph ph-lock-key"></i>
                </div>

                <div class="subscription-placeholder-info">

                    <strong>
                        Conteúdo exclusivo
                    </strong>

                    <span>
                        Em breve você poderá assinar e ter
                        acesso às nossas aulas de investimento.
                    </span>

                </div>

                <button
                    type="button"
                    class="subscription-placeholder-btn"
                    disabled
                >
                    Em breve
                </button>

            </div>

        </div>


        <div class="perfil-divider"></div>


        <div class="perfil-content">

            <h2>
                Configurações
            </h2>


            <div class="perfil-option">

                <div class="option-info">

                    <div class="option-icon">

                        <i class="ph ph-palette"></i>

                    </div>

                    <div>

                        <strong>
                            Aparência
                        </strong>

                        <span>
                            Alternar entre tema claro e escuro
                        </span>

                    </div>

                </div>


                <button
                    type="button"
                    id="theme-toggle"
                    class="theme-switch"
                    role="switch"
                    aria-checked="false"
                    aria-label="Alternar tema"
                    onclick="PinguTheme.toggle(); requestAnimationFrame(syncThemeSwitch);"
                >

                    <i class="ph ph-sun"></i>

                    <i class="ph ph-moon"></i>

                    <span class="theme-switch-thumb"></span>

                </button>

            </div>

        </div>


        <div class="perfil-divider"></div>

        <div class="perfil-actions">

            <button
                class="perfil-action logout"
                type="button"
                onclick="window.location.href='../actions/logout.php'"
            >

                <span class="action-icon">

                    <i class="ph ph-sign-out"></i>

                </span>


                <span>

                    <strong>
                        Sair da conta
                    </strong>

                    <small>
                        Encerrar sua sessão atual
                    </small>

                </span>


                <i class="ph ph-caret-right action-arrow"></i>

            </button>

        </div>

    </section>

</main>


<?php require_once "../includes/footer.php"; ?>


<script>


const picOptions = document.querySelector(".perfil-pic-opts");
const fileImg = document.querySelector("#foto");


function togglePicForm() {

    picOptions.classList.toggle("hidden-pic");

}


/* Upload automático */

if (fileImg) {

    fileImg.addEventListener("change", function () {

        if (this.files && this.files.length > 0) {
            this.closest("form").submit();
        }

    });

}


/* Fecha o menu clicando fora */

document.addEventListener("click", function (event) {

    const editButton =
        document.querySelector(".avatar-edit");

    if (
        !picOptions.classList.contains("hidden-pic") &&
        !picOptions.contains(event.target) &&
        !editButton.contains(event.target)
    ) {

        picOptions.classList.add("hidden-pic");

    }

});


function syncThemeSwitch() {

    const themeToggleBtn =
        document.getElementById("theme-toggle");

    if (!themeToggleBtn) return;

    const isLight =
        document.documentElement.dataset.theme === "light";

    themeToggleBtn.setAttribute(
        "aria-checked",
        isLight ? "true" : "false"
    );

}


syncThemeSwitch();

</script>
</body>
</html>
