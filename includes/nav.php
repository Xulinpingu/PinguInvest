<nav class="navbar">
    <ul class="menu" id="menu">
        <li><a href="<?= isset($_SESSION['id_usuario']) ? '../pages/perfil.php' : '../pages/login.php' ?>">Perfil</a></li>
        <li><a href="../pages/wallet.php">Carteira</a></li>
        <li><a href="#">Mercado</a></li>
        <li><a href="#">Aulas</a></li>
    </ul>
</nav>