<?php

session_start();

require_once "../config/connDB.php";

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci minha senha</title>
    <link rel="icon" type="image/png" href="../assets/images/logo/pinguin.png">

    <!-- Aplica o tema salvo (localStorage) antes de renderizar a página, evitando flash do tema errado -->
    <script src="../assets/js/theme.js"></script>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <script defer src="../assets/js/functions.js"></script>
    <script defer src="../assets/js/particles.js"></script>
    <script defer src="../assets/js/app.js"></script>
</head>
<body class="auth-body">
    <div id="particles-js"></div>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="card-circle"></div>

            <a href="login.php" class="auth-back-btn" aria-label="Voltar para o login">
                <i class="ph ph-arrow-left"></i>
            </a>

            <div class="auth-header">
                <img src="../assets/images/logo/pinguin.png" alt="PinguInvest">
                <h1>Esqueceu a senha?</h1>
                <p>Sem problemas. Informe seu e-mail e enviaremos um link para você redefinir sua senha.</p>
            </div>

            <?php if (isset($_SESSION['aviso'])): ?>
                <div class="alerta <?= $_SESSION['aviso_tipo'] ?>">
                    <?php 
                        echo $_SESSION['aviso']; 
                        // Limpa o aviso da sessão para sumir no próximo refresh
                        unset($_SESSION['aviso']);
                        unset($_SESSION['aviso_tipo']);
                    ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" action="../actions/send_email.php" method="POST">

                <div class="auth-input-group">
                    <label for="email">E-mail</label>
                    <div class="auth-input-wrap">
                        <i class="ph ph-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="seuemail@exemplo.com" required>
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn">Enviar link para redefinir senha</button>
            </form>

            <div class="auth-footer">
                Lembrou a senha? <a href="login.php">Fazer login</a>
            </div>
        </div>
    </div>
</body>
</html>
