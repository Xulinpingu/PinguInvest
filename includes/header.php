<header>
    <img src="../assets/images/logo/pinguin.png" alt="Logo">

    <h1 id="page-title"></h1>

    <button class="hamburger" onclick="toggleMenu()">
        <div id="menu-icon"></div>
    </button>
</header>

<script>
    document.getElementById("page-title").textContent = document.title;
</script>

<?php 
require_once "nav.php";
?>