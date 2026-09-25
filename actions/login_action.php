<?php

require_once "../config/connDB.php";

session_start();

$email = $_POST['email'] ?? '';
$password = $_POST['senha'] ?? '';

$sql = " SELECT id_usuario, senha FROM usuarios
        WHERE email = :email";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':email' => $email
]);

$usuario_senha = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario_senha && password_verify($password, $usuario_senha['senha'])) {

    $_SESSION['id_usuario'] = $usuario_senha['id_usuario'];
    $_SESSION['email'] = $email;

    if (isset($_POST['lembrar'])) {
        $token = bin2hex(random_bytes(16));

        $sql = "UPDATE usuarios 
                SET remember_token = :remember_token
                WHERE id_usuario = :id_usuario";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':remember_token' => $token,
            ':id_usuario' => $usuario_senha['id_usuario']
        ]);

        setcookie('lembrar', $usuario_senha['id_usuario'] . ':' . $token, time() + 30*24*60*60, '/');
    }

    header("Location: ../pages/perfil.php");
} else {
    $_SESSION['aviso'] = "E-mail ou senha incorretos.";
    $_SESSION['aviso_tipo'] = "danger";

    header("Location: ../pages/login.php");
}

?>