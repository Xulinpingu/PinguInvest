<?php

session_start();

require_once "../config/connDB.php";

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$email = $_POST['email'] ?? null;

$sql = "SELECT * FROM usuarios 
        WHERE email = :email";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':email' => $email
]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($user){
    $token = bin2hex(random_bytes(16));

    $sql = "UPDATE usuarios 
            SET reset_token = :reset_token 
            WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':reset_token' => $token,
        ':id_usuario' => $user['id_usuario']
    ]);

    $link = "http://localhost/PinguInvest/pages/redefinir_senha.php?token=$token";

    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'xulinpingu.equipe@gmail.com';
    $mail->Password = 'dpjisbcjhbyeffdr';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('xulinpingu.equipe@gmail.com', 'Site de Investimentos');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Redefinir senha';
    $mail->Body = "Clique no link para redefinir sua senha: <a href='$link'>Clique aqui</a>";

    $mail->send();

    header("Location: ../pages/login.php");
}
else{
    $_SESSION['aviso'] = "E-mail inválido.";
    $_SESSION['aviso_tipo'] = "danger";

    header("Location: ../pages/checkEmail_redefinirSenha.php");
}