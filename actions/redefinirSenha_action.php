<?php 

session_start();

require_once "../config/connDB.php";

$token = $_POST['token'] ?? '';
$senha = $_POST['senha-nova'] ?? '';
$confirmar_senha = $_POST['confirmar-senha-nova'] ?? '';

if ($senha !== $confirmar_senha) {
    $_SESSION['aviso'] = "As senhas não coincidem.";
    $_SESSION['aviso_tipo'] = "danger";

    header("Location: ../pages/redefinir_senha.php?token=$token");
    exit();
}

$sql = "SELECT * FROM usuarios
        WHERE reset_token = :reset_token";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':reset_token' => $token
]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($user){
    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "UPDATE usuarios
            SET senha = :senha, reset_token = NULL
            WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':senha' => $hash,
        ':id_usuario' => $user['id_usuario']
    ]);

    $_SESSION['aviso'] = "Senha redefinida com sucesso.";
    $_SESSION['aviso_tipo'] = "success";

    header("Location: ../pages/login.php");
    exit();
}
else{
    $_SESSION['aviso'] = "Link inválido.";
    $_SESSION['aviso_tipo'] = "danger";

    header("Location: ../pages/redefinir_senha.php?token=$token");
    exit();
}