<?php 

require_once "../config/connDB.php";

$id_secao = $_POST['id_secao'];
$aulas_add = $_POST['aulas'] ?? [];

$sql = "INSERT INTO aula_secao(id_aula, id_secao) VALUES (:id_aula, $id_secao)";
$stmt = $pdo->prepare($sql);

foreach($aulas_add as $aula){
    $stmt->execute([
        ":id_aula" => $aula
    ]);
}

header("Location: ../pages/aulas.php");