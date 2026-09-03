<?php

require_once "../config/connDB.php";

session_start();

$email = $_POST['email'] ?? '';
$password = $_POST['senha'] ?? '';

$sql = " SELECT id_usuario,senha FROM usuarios
    WHERE email = :email";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':email' => $email,
]);

$usuario_senha = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario_senha && password_verify($password, $usuario_senha['senha'])) {

    $_SESSION['id_usuario'] = $usuario_senha['id_usuario'];
    $_SESSION['email'] = $email;

    header("Location: ../pages/perfil.php");
} else {
    $_SESSION['aviso'] = "E-mail ou senha incorretos.";
    $_SESSION['aviso_tipo'] = "danger";

    header("Location: ../pages/login.php");
}

?>