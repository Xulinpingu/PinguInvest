<?php

session_start();

require_once "../config/connDB.php";

$new_name = $_GET['nome'] ?? '';
$try_name = str_replace(' ', '', $new_name);

if (!empty($try_name)) {
    $sql = "UPDATE usuarios
            SET nome = :nome
            WHERE id_usuario = :id_usuario";
    $stmtUpdate = $pdo->prepare($sql);

    $stmtUpdate->execute([
        ':nome' => $new_name,
        ':id_usuario' => $_SESSION['id_usuario']
    ]);
}

Header("Location: ../pages/perfil.php");