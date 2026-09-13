<?php

session_start();

require_once '../config/connDB.php';

$idUser = $_SESSION['id_usuario'];
$plano = null;
$plano_tempo = 0;

if($_POST["mes"]){
    $plano = "mensal";

    $plano_tempo = 1; 
} 
else if($_POST["tri"]){
    $plano = "trimestral";

    $plano_tempo = 3;
}
else if($_POST["ano"]){
    $plano = "anual";

    $plano_tempo = 12;
}

$sql = "INSERT INTO assinaturas(id_usuario, status, plano) VALUES (:id_usuario, 'ativa', :plano)";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_usuario' => $idUser,
    ':plano' => $plano
]);

$sql = "SELECT id_assinatura FROM assinaturas
        WHERE id_usuario = :id_usuario
        ORDER BY data_inicio DESC
        LIMIT 1";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_usuario' => $idUser
]);

$id_assinatura = $stmt->fetch(PDO::FETCH_COLUMN);

$sql = "SELECT DATE_ADD(data_inicio, INTERVAL :plano_tempo MONTH) AS data_fim FROM assinaturas
        WHERE id_assinatura = :id_assinatura";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':plano_tempo' => $plano_tempo,
    ':id_assinatura' => $id_assinatura
]);

$data_fim = $stmt->fetch(PDO::FETCH_COLUMN);

$sql = "UPDATE assinaturas
        SET data_fim = :data_fim
        WHERE id_assinatura = :id_assinatura";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':data_fim' => $data_fim,
    ':id_assinatura' => $id_assinatura
]);

header("Location: ../pages/aulas.php");

?>