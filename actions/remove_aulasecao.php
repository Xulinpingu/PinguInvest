<?php

require_once "../config/connDB.php";

$id_aula = $_POST['id-aula'];
$id_secao = $_POST['id-secao'];

$sql = "DELETE FROM aula_secao
        WHERE id_aula = :id_aula AND id_secao = :id_secao";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id_aula" => $id_aula,
    ":id_secao" => $id_secao
]);

header("Location: ../pages/aulas.php");