<div class="page-loader" id="page-loader" aria-hidden="true">
    <div class="page-loader-mark">
        <img src="../assets/images/logo/pinguin.png" alt="">
        <span></span>
    </div>
</div>

<header class="site-header">
    <a href="../pages/index.php" class="site-brand" aria-label="PinguInvest — início">
        <img src="../assets/images/logo/pinguin.png" alt="">
        <div class="site-brand-copy">
            <strong>PinguInvest</strong>
            <span id="page-title"></span>
        </div>
    </a>

    <?php require __DIR__ . "/nav.php"; ?>

    <button class="hamburger" type="button" onclick="toggleMenu()" aria-label="Abrir menu" aria-controls="menu" aria-expanded="false">
        <div id="menu-icon"></div>
        <span class="hamburger-fallback" aria-hidden="true"><i></i><i></i><i></i></span>
    </button>
</header>

<script>
    (function () {
        var title = document.title === 'PinguInvest' ? 'Início' : document.title;
        var pageTitle = document.getElementById('page-title');
        if (pageTitle) pageTitle.textContent = title;
    })();
</script>
<script defer src="../assets/js/interactions.js"></script>
