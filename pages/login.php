<?php

session_start();

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Aplica o tema salvo (localStorage) antes de renderizar a página, evitando flash do tema errado -->
    <script src="../assets/js/theme.js"></script>

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

    <script src="https://accounts.google.com/gsi/client" async></script>
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
                <button type="button" id="google-signin-btn" class="auth-social-btn google-btn">
                    <svg class="google-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Google
                </button>
                <!-- Botão real do Google, renderizado escondido. O botão estilizado acima
                     dispara um clique nele para abrir o fluxo oficial do Google. -->
                <div id="google-button" class="google-button-hidden"></div>

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

        function iniciarGoogle() {
            google.accounts.id.initialize({
                // Substitua pelo Client ID gerado no Google Cloud Console
                // (Credenciais > ID do cliente OAuth 2.0 > tipo "Aplicativo da Web").
                client_id: "a.apps.googleusercontent.com",
                callback: handleGoogleLogin
            });

            // Renderiza o botão oficial do Google dentro do container escondido.
            // É ele quem realmente abre o popup de login do Google.
            google.accounts.id.renderButton(
                document.getElementById("google-button"),
                { type: "standard", theme: "outline", size: "large", width: 300 }
            );
        }

        window.addEventListener("load", iniciarGoogle);

        // Nosso botão estilizado (igual ao da Apple) só serve de "gatilho":
        // ao ser clicado, ele aciona o clique no botão real do Google.
        document.getElementById("google-signin-btn").addEventListener("click", function () {
            const realGoogleBtn = document.querySelector("#google-button div[role=button]");
            if (realGoogleBtn) {
                realGoogleBtn.click();
            } else {
                console.error("O botão do Google ainda não carregou. Tente novamente em instantes.");
            }
        });

        function handleGoogleLogin(response) {
            // response.credential é o JWT (ID token) do Google.
            // Enviamos para o back-end validar e criar a sessão do usuário.
            fetch("../pages/auth/google-login.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "credential=" + encodeURIComponent(response.credential)
            })
                .then(res => res.text())
                .then(() => {
                    window.location.href = "wallet.php";
                })
                .catch(err => console.error("Erro no login com Google:", err));
        }

    </script>
</body>
</html>