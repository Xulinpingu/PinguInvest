<?php

session_start();

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/logo/pinguin.png">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <script defer src="../assets/js/functions.js"></script>
    <script defer src="../assets/js/particles.js"></script>
    <script defer src="../assets/js/app.js"></script>

    <link
      rel="stylesheet"
      type="text/css"
      href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"
    />
</head>
<body class="auth-body">
    <div id="particles-js"></div>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="card-circle"></div>

            <button type="button" class="auth-back-btn" onclick="window.location.href = 'index.php'">
                <i class="ph ph-arrow-left"></i>
            </button>

            <div class="auth-header">
                <img src="../assets/images/logo/pinguin.png" alt="PinguInvest">
                <h1>Bem-vindo de volta</h1>
                <p>Entre com sua conta para continuar investindo</p>
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

            <form class="auth-form" action="../actions/login_action.php" method="POST">

                <div class="auth-input-group">
                    <label for="email">E-mail</label>
                    <div class="auth-input-wrap">
                        <i class="ph ph-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="seuemail@exemplo.com" required>
                    </div>
                </div>

                <div class="auth-input-group">
                    <label for="senha">Senha</label>
                    <div class="auth-input-wrap">
                        <i class="ph ph-lock"></i>
                        <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                        <i class="ph ph-eye toggle-pass" onclick="togglePassword(this, 'senha')"></i>
                    </div>
                </div>

                <div class="auth-options">
                    <label class="auth-checkbox">
                        <input type="checkbox" name="lembrar">
                        Lembrar-me
                    </label>
                    <a class="auth-forgot" href="#">Esqueceu a senha?</a>
                </div>

                <button type="submit" class="auth-submit-btn">Entrar</button>
            </form>

            <div class="auth-divider">ou continue com</div>

            <div class="auth-socials">
                <a href="#" class="auth-social-btn google-btn">
                    <i class="ph ph-google-logo"></i>
                    Google
                </a>
                <a href="#" class="auth-social-btn apple-btn">
                    <i class="ph ph-apple-logo"></i>
                    Apple
                </a>
            </div>

            <div class="auth-footer">
                Ainda não tem uma conta? <a href="cadastro.php">Criar conta</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(icon, inputId) {
            const input = document.getElementById(inputId);
            const isHidden = input.type === "password";
            input.type = isHidden ? "text" : "password";
            icon.classList.toggle("ph-eye");
            icon.classList.toggle("ph-eye-slash");
        }
    </script>
</body>
</html>