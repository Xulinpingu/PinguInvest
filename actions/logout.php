<?php

session_start();
require_once '../config/connDB.php';

if (isset($_SESSION['id_usuario'])) {
    $sql = "UPDATE usuarios
            SET remember_token = NULL
            WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario' => $_SESSION['id_usuario'],
    ]);
}

setcookie('lembrar', '', time() - 3600, '/');

session_unset();
session_destroy();

// O tema fica no localStorage do navegador, então o PHP sozinho não consegue
// apagá-lo. Esta resposta curta reseta a preferência antes de voltar ao login.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Saindo...</title>
    <script>
        try {
            localStorage.setItem('pingu-theme', 'dark');
        } catch (e) {}
        window.location.replace('../pages/login.php');
    </script>
    <noscript>
        <meta http-equiv="refresh" content="0;url=../pages/login.php">
    </noscript>
</head>
<body></body>
</html>
