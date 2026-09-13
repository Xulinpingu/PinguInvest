<?php

require_once "../config/connDB.php";

$id_aula = $_POST['id-aula'];

$sql = "DELETE FROM aulas 
        WHERE id_aula = :id_aula";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id_aula" => $id_aula
]);

header("Location: ../pages/aulas.php");