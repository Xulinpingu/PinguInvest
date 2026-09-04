<?php

session_start();

$logado = isset($_SESSION['id_usuario']);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PinguInvest</title>
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

    <main class="home">

        <!-- Hero -->
        <section class="home-hero">
            <div class="hero-content">
                <img src="../assets/images/logo/pinguin.png" alt="PinguInvest" class="hero-logo">

                <span class="hero-badge"><i class="ph ph-sparkle"></i> Investimentos + educação financeira</span>

                <h1>Seus investimentos.<br>Seu patrimônio.<br><span>Seu conhecimento.</span></h1>

                <p class="hero-subtitle">
                    Uma plataforma web para organizar seus investimentos e desenvolver
                    sua educação financeira — tudo em um só lugar.
                </p>

                <div class="hero-actions">
                    <?php if ($logado): ?>
                        <a href="wallet.php" class="hero-btn-primary">
                            Ir para minha carteira <i class="ph ph-arrow-right"></i>
                        </a>
                        <a href="perfil.php" class="hero-btn-secondary">Meu perfil</a>
                    <?php else: ?>
                        <a href="cadastro.php" style="text-decoration: none;">
                            <button class="cssbuttons-io-button"">
                                Criar Conta
                                <div class="icon">
                                    <i class="ph ph-arrow-left"></i>
                                </div>
                            </button>
                        </a>
                        <a href="login.php" class="hero-btn-secondary">Já tenho conta</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Problema -->
        <section class="home-section home-panel">
            <h2>Chega de planilhas espalhadas</h2>
            <p>
                Sabemos que acompanhar seus investimentos pode ser complicado quando é
                necessário utilizar planilhas complexas ou várias plataformas diferentes.
                Informações espalhadas dificultam a organização, o controle financeiro e
                o acompanhamento da evolução do patrimônio.
            </p>
            <p>
                O PinguInvest reúne as principais ferramentas para você organizar e
                acompanhar sua vida financeira em um único lugar, com uma experiência
                simples e intuitiva.
            </p>
        </section>

        <!-- O que você pode fazer -->
        <section class="home-section home-features">
            <span class="home-eyebrow">O que você pode fazer</span>
            <h2>Tudo o que você precisa, em um só lugar</h2>

            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="ph ph-wallet"></i></div>
                    <h3>Carteira de investimentos</h3>
                    <p>Tenha uma visão organizada dos seus investimentos e acompanhe sua carteira com mais facilidade.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="ph ph-arrows-left-right"></i></div>
                    <h3>Movimentações</h3>
                    <p>Registre suas movimentações financeiras e mantenha seu controle sempre atualizado.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="ph ph-chart-line-up"></i></div>
                    <h3>Patrimônio</h3>
                    <p>Acompanhe a evolução do seu patrimônio e tenha uma visão mais clara do seu progresso.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="ph ph-graduation-cap"></i></div>
                    <h3>Educação financeira</h3>
                    <p>Acesse conteúdos educativos para aprender mais sobre investimentos e melhorar seus conhecimentos financeiros.</p>
                </div>
            </div>
        </section>

        <!-- Mais do que números -->
        <section class="home-section home-panel">
            <div class="highlight-icon"><i class="ph ph-target"></i></div>
            <h2>Mais do que acompanhar números</h2>
            <p>
                O PinguInvest não foi criado apenas para mostrar dados. Nosso objetivo é
                facilitar a organização financeira e ajudar você a entender melhor seus
                investimentos, tornando suas decisões mais conscientes.
            </p>
        </section>

        <!-- Time -->
        <section class="home-section home-team">
            <span class="home-eyebrow">Quem está por trás</span>
            <h2>Feito por quem também investe</h2>
            <p class="home-team-intro">
                O PinguInvest foi desenvolvido unindo tecnologia, investimentos e
                educação financeira em um único projeto.
            </p>

            <div class="team-grid">
                <div class="team-card">
                    <div class="team-avatar"><i class="ph ph-user-circle"></i></div>
                    <strong>Bruno Lourenço de Lima</strong>
                </div>
                <div class="team-card">
                    <div class="team-avatar"><i class="ph ph-user-circle"></i></div>
                    <strong>Henrique Silvestre Martin</strong>
                </div>
                <div class="team-card">
                    <div class="team-avatar"><i class="ph ph-user-circle"></i></div>
                    <strong>Isaac Faleiros Quevedo</strong>
                </div>
            </div>
        </section>

        <!-- CTA final -->
        <?php if (!$logado): ?>
        <section class="home-cta">
            <h2>Pronto para organizar seus investimentos?</h2>
            <p>Crie sua conta gratuita e comece a acompanhar seu patrimônio hoje mesmo.</p>
            <a href="cadastro.php" style="text-decoration: none;">
                <button class="cssbuttons-io-button">
                    Comece hoje!
                    <div class="icon">
                        <i class="ph ph-arrow-left"></i>
                    </div>
                </button>
            </a>
        </section>
        <?php endif; ?>

    </main>

    <?php require_once "../includes/footer.php" ?>
</body>

<style>

/* From Uiverse.io by adamgiebl */ 
.cssbuttons-io-button {
  background: var(--terciary-color);
  color: white;
  font-family: inherit;
  padding: 0.35em;
  padding-left: 1.2em;
  font-size: 17px;
  font-weight: 500;
  border-radius: 0.9em;
  border: none;
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  box-shadow: inset 0 0 1.6em -0.6em #714da6;
  overflow: hidden;
  position: relative;
  height: 2.8em;
  padding-right: 3.3em;
  cursor: pointer;
  width: 100%;
}

.cssbuttons-io-button .icon {
  background: white;
  margin-left: 1em;
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 2.2em;
  width: 2.2em;
  border-radius: 0.7em;
  box-shadow: 0.1em 0.1em 0.6em 0.2em #7b52b9;
  right: 0.3em;
  transition: all 0.3s;
  color: black;
}

.cssbuttons-io-button:hover .icon {
  width: calc(100% - 0.6em);
}

.cssbuttons-io-button .icon svg {
  width: 1.1em;
  transition: transform 0.3s;
  color: #7b52b9;
}

.cssbuttons-io-button:hover .icon svg {
  transform: translateX(0.1em);
}

.cssbuttons-io-button:active .icon {
  transform: scale(0.95);
}

.icon i {
    color: black;
    font-weight: 700;
}


</style>
</html>
