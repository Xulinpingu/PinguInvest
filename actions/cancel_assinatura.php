<?php

session_start();

require_once "../config/connDB.php";

$idUser = $_SESSION['id_usuario'];

$sql = "SELECT id_assinatura FROM assinaturas
        WHERE id_usuario = :id_usuario
        ORDER BY data_inicio DESC
        LIMIT 1";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_usuario' => $idUser
]);

$id_assinatura = $stmt->fetch(PDO::FETCH_COLUMN);

$sql = "SELECT status FROM assinaturas
        WHERE id_assinatura = :id_assinatura";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_assinatura' => $id_assinatura
]);

$assinatura_status = $stmt->fetch(PDO::FETCH_COLUMN);

if ($assinatura_status == 'ativa'){
    $sql = "UPDATE assinaturas
            SET status = 'cancelada'
            WHERE id_assinatura = :id_assinatura";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
            ':id_assinatura' => $id_assinatura
    ]);
}

header("Location: ../pages/perfil.php");
