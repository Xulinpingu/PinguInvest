<?php

session_start();

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>

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
                <h1>Crie sua conta</h1>
                <p>Comece a organizar seus investimentos agora mesmo</p>
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

            <form class="auth-form Cadastro-Form" action="../actions/cadastro_action.php" method="POST">

                <div class="auth-input-group">
                    <label for="nome">Nome completo</label>
                    <div class="auth-input-wrap">
                        <i class="ph ph-user"></i>
                        <input type="text" id="nome" name="nome" placeholder="Seu nome completo" value="<?= $_SESSION['name_placeholder'] ?? '' ?>" required>
                    </div>
                </div>

                <div class="auth-input-group">
                    <label for="email">E-mail</label>
                    <div class="auth-input-wrap">
                        <i class="ph ph-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="seuemail@exemplo.com" value="<?= $_SESSION['email_placeholder'] ?? '' ?>" required>
                    </div>
                </div>

                <?php
                    unset($_SESSION['name_placeholder']);
                    unset($_SESSION['email_placeholder']);

                ?>

                <div class="auth-input-group">
                    <label for="senha">Senha</label>
                    <div class="auth-input-wrap">
                        <i class="ph ph-lock"></i>
                        <input type="password" id="senha" name="senha" placeholder="Crie uma senha" required>
                        <i class="ph ph-eye toggle-pass" onclick="togglePassword(this, 'senha')"></i>
                    </div>
                </div>

                <div class="auth-input-group">
                    <label for="confirmar_senha">Confirmar senha</label>
                    <div class="auth-input-wrap">
                        <i class="ph ph-lock"></i>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a senha" required>
                        <i class="ph ph-eye toggle-pass" onclick="togglePassword(this, 'confirmar_senha')"></i>
                    </div>
                </div>

                <label class="auth-checkbox">
                    <input type="checkbox" name="termos" required>
                    Concordo com os termos de uso e política de privacidade
                </label>

                <button type="submit" class="auth-submit-btn">Criar conta</button>
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
                Já tem uma conta? <a href="login.php">Fazer login</a>
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
