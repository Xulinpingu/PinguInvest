<?php

require_once "../config/connDB.php";

session_start();

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

if ($senha !== $confirmar_senha) {
    $_SESSION['aviso'] = "As senhas não coincidem.";
    $_SESSION['aviso_tipo'] = "danger";

    $_SESSION['name_placeholder'] = $nome;
    $_SESSION['email_placeholder'] = $email;

    header("Location: ../pages/cadastro.php?error=As senhas não coincidem.");
    exit();
}

$senha = password_hash($senha, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO usuarios (nome, email, senha, foto) VALUES (:nome, :email, :senha, :foto)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':senha' => $senha,
        ':foto' => "../assets/uploads/icon_perfil/placeholder.png"
    ]);

    $_SESSION['aviso'] = "Cadastro realizado com sucesso!";
    $_SESSION['aviso_tipo'] = "success";

    header("Location: ../pages/login.php");
    exit();
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Código de erro para violação de chave única
        $_SESSION['aviso'] = "E-mail já cadastrado.";
        $_SESSION['aviso_tipo'] = "danger";
    } else {
        $_SESSION['aviso'] = "Erro ao cadastrar usuário: " . $e->getMessage();
        $_SESSION['aviso_tipo'] = "danger";
    }

    $_SESSION['name_placeholder'] = $nome;
    $_SESSION['email_placeholder'] = $email;

    header("Location: ../pages/cadastro.php");
    exit();
}

?>