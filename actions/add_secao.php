<?php

require_once '../config/connDB.php';

$nome_secao = $_POST['secao-name'] ?? null;

$sql = "INSERT INTO secoes (nome) VALUES (:nome_secao)";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':nome_secao' => $nome_secao
]);

header("Location: ../pages/aulas.php");