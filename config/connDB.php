<?php

// 1. Conexão
$dsn = "mysql:host=localhost;dbname=PinguInvest_DB;charset=utf8";
$usuario = "root";
$senha = "";

// Conexão com o Banco de Dados
try {
    // Criamos o objeto PDO
    $pdo = new PDO($dsn, $usuario, $senha);
} catch (PDOException $e) { //Apenas será executado se acontecer algum erro
    die("Erro ao conectar: " . $e->getMessage());
}

?>