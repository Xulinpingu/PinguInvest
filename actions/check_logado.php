<?php

$logado = isset($_SESSION['id_usuario']);

if (!$logado && isset($_COOKIE['lembrar'])) {
    $cookieParts = explode(':', (string) $_COOKIE['lembrar'], 2);

    if (count($cookieParts) === 2) {
        [$id_usuario, $token] = $cookieParts;

        // Páginas públicas como Início e Mercado não precisam abrir conexão
        // com o banco a cada visita. A conexão só é necessária se houver um
        // cookie de "lembrar de mim" para validar.
        if (!isset($pdo)) {
            require_once __DIR__ . '/../config/connDB.php';
        }

        $sql = "SELECT * FROM usuarios
                WHERE id_usuario = :id_usuario AND remember_token = :remember_token";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':remember_token' => $token,
        ]);

        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['id_usuario'] = $id_usuario;
            $logado = true;
        }
    }
}
