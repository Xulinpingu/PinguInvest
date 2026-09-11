<?php

session_start();

require_once '../config/connDB.php';

$idUser = $_SESSION['id_usuario'];
$plano = null;

if($_POST["mes"]){
    $plano = "mensal";
} 
else if($_POST["tri"]){
    $plano = "trimestral";
}
else if($_POST["ano"]){
    $plano = "anual";
}

$sql = "INSERT INTO assinaturas(id_usuario, status, plano) VALUES (:id_usuario, 'ativa', :plano)";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_usuario' => $idUser,
    ':plano' => $plano
]);

$sql = "";

header("Location: ../pages/aulas.php");

?>