<?php
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$loggedIn = isset($_SESSION['id_usuario']);

$navItems = [
    ['file' => 'index.php', 'label' => 'Início', 'icon' => 'ph-house'],
    ['file' => 'wallet.php', 'label' => 'Carteira', 'icon' => 'ph-wallet'],
    ['file' => 'mercado.php', 'label' => 'Mercado', 'icon' => 'ph-chart-line-up'],
    ['file' => 'aulas.php', 'label' => 'Aulas', 'icon' => 'ph-graduation-cap'],
];
?>

<nav class="navbar" aria-label="Navegação principal">
    <ul class="menu" id="menu">
        <?php foreach ($navItems as $item):
            $active = $currentPage === $item['file'];
        ?>
            <li>
                <a href="../pages/<?= htmlspecialchars($item['file']) ?>" <?= $active ? 'class="active" aria-current="page"' : '' ?>>
                    <i class="ph <?= htmlspecialchars($item['icon']) ?>"></i>
                    <span><?= htmlspecialchars($item['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>

        <li class="nav-profile-item">
            <a href="<?= $loggedIn ? '../pages/perfil.php' : '../pages/login.php' ?>" <?= $currentPage === 'perfil.php' ? 'class="active" aria-current="page"' : '' ?>>
                <i class="ph <?= $loggedIn ? 'ph-user-circle' : 'ph-sign-in' ?>"></i>
                <span><?= $loggedIn ? 'Perfil' : 'Entrar' ?></span>
            </a>
        </li>
    </ul>
</nav>
