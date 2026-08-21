<?php

session_start();

require_once "../config/connDB.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.php");
    exit();
}
else{
    $idUser = $_SESSION['id_usuario'];
}

$sql = "
    SELECT * FROM usuarios
    WHERE id_usuario = :id_usuario";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id_usuario' => $idUser
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="icon" type="image/png" href="../assets/images/logo/pinguin.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <script defer src="../assets/js/functions.js"></script>
</head>
<body>
    <?php require_once "../includes/header.php" ?>

    <h2>Meu Perfil</h2>
    <p>Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!</p>

    <button id="btn-logout" onclick="window.location.href='../actions/logout.php'">log-out</button>

    <?php require_once "../includes/footer.php" ?>

</body>
</html>