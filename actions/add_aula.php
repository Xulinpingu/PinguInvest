<?php

require_once '../config/connDB.php';

$nome_aula = $_POST['video-name'] ?? null;
$link_aula = $_POST['video-link'] ?? null;
$gratis = $_POST['gratis'] ?? null;

$link_aula = str_replace("watch?v=", "embed/", $link_aula);

$sql = "INSERT INTO aulas (nome, link, gratis) VALUES (:nome_aula, :link_aula, :gratis)";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':nome_aula' => $nome_aula,
    ':link_aula' => $link_aula,
    ':gratis' => $gratis
]);

header("Location: ../pages/aulas.php");