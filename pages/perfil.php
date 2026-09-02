<?php

session_start();

require_once "../config/connDB.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.php");
    exit();
}
else{
    $idUser = $_SESSION['id_usuario'];
}

$sql = "
    SELECT * FROM usuarios
    WHERE id_usuario = :id_usuario";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id_usuario' => $idUser
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="icon" type="image/png" href="../assets/images/logo/pinguin.png">

    <!-- Aplica o tema salvo (localStorage) antes de renderizar a página, evitando flash do tema errado -->
    <script src="../assets/js/theme.js"></script>

    <link rel="stylesheet" href="../assets/css/style.css">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <script defer src="../assets/js/functions.js"></script>

    <link
      rel="stylesheet"
      type="text/css"
      href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"
    />
</head>
<body>
    <?php require_once "../includes/header.php" ?>

    <section class="perfil-section">
        <div class="perfil-pic">
            <img src="<?= $usuario['foto'] ?>" alt="" width="150px" height="150px" style="border-radius: 50%; object-fit: cover;">
            <button onclick="TooglePicForm()" class="perfil-btn btn-pic-edit">Editar <i class="ph ph-pencil-simple" style="color: var(--text-d);"></i></button>

            <div class="perfil-pic-opts">
                <form action="../actions/change_icon.php" class="hidden-pic upload-pic" enctype="multipart/form-data" method="POST">
                    <label for="foto" class="btn-upload perfil-btn" style="background-color: var(--border-d);"> Upload</label>
                    <input type="file" name="foto" id="foto" class="hidden-pic">
                </form>
                <br>
                <form action="../actions/change_icon.php" class="hidden-pic exclude-pic" method="POST">
                    <button type="submit" class="perfil-btn" style="background-color: var(--border-d);">Excluir</button>
                </form>
            </div>
        </div>

        <div class="perfil-info">
            <h2>Meu Perfil</h2>
            <p>Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!</p>

            <div class="perfil-theme-toggle">
                <span>Tema</span>
                <button
                    type="button"
                    id="theme-toggle"
                    class="theme-switch"
                    role="switch"
                    aria-checked="false"
                    aria-label="Alternar entre tema claro e escuro"
                    onclick="PinguTheme.toggle(); syncThemeSwitch();"
                >
                    <i class="ph ph-sun theme-switch-icon theme-switch-icon-sun"></i>
                    <i class="ph ph-moon theme-switch-icon theme-switch-icon-moon"></i>
                    <span class="theme-switch-thumb"></span>
                </button>
            </div>

            <br>
            <button class="perfil-btn" id="btn-logout" onclick="window.location.href='../actions/logout.php'">Logout</button>
        </div>     
    </section>

    <?php require_once "../includes/footer.php" ?>

</body>

<script> 

    const form_exclude = document.querySelector(".exclude-pic");
    const form_upload = document.querySelector(".upload-pic");
    const file_img = document.querySelector("#foto");

    file_img.addEventListener('change', function() {
        // Verifica se o usuário realmente selecionou um arquivo
        if (this.files && this.files.length > 0) {
            form_upload.submit();
        }
    });

    function TooglePicForm(){
        form_exclude.classList.toggle("hidden-pic");
        form_upload.classList.toggle("hidden-pic");
    }

    document.addEventListener("click", function(event) {
        if (form_exclude.classList.contains("hidden-pic") === false && !form_exclude.contains(event.target) && !event.target.matches(".btn-pic-edit") && !form_upload.contains(event.target)) {
            form_exclude.classList.toggle("hidden-pic"); 
            form_upload.classList.toggle("hidden-pic");      
        }
    });

    // Tema claro/escuro
    function syncThemeSwitch() {
        const themeToggleBtn = document.getElementById("theme-toggle");
        if (!themeToggleBtn) return;
        themeToggleBtn.setAttribute("aria-checked", PinguTheme.get() === "light" ? "true" : "false");
    }

    syncThemeSwitch();

</script>

</html>