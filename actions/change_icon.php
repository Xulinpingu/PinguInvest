<?php

session_start();

require_once "../config/connDB.php";

$tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

$pasta = "../assets/uploads/icon_perfil/";

if (isset($_FILES['foto'])) {

    $image = $_FILES['foto'];

    if ($image['size'] > 16 * 1024 * 1024) {
        die("A imagem deve ter no máximo 16 MB.");
    }


    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipo = $finfo->file($image['tmp_name']);

    if (!isset($tiposPermitidos[$tipo])) {
        die("Formato de imagem não permitido.");
    }

    $extensao = $tiposPermitidos[$tipo];
    $nome_foto = $_SESSION["id_usuario"] . "." . $extensao;

    $caminho = $pasta . $nome_foto;

    foreach ($tiposPermitidos as $extensao) {
        $caminho_antigo = $pasta . $_SESSION["id_usuario"] . "." . $extensao;
        if (file_exists($caminho_antigo)) {
            unlink($caminho_antigo);
        }
    }

    $sql = "UPDATE usuarios
            SET foto = :foto
            WHERE id_usuario = :id_usuario";
    $stmtUpdate = $pdo->prepare($sql);

    $stmtUpdate->execute([
        ':foto' => $caminho,
        ':id_usuario' => $_SESSION['id_usuario']
    ]);

    if (move_uploaded_file($image['tmp_name'], $caminho)) {
        echo "Imagem salva com sucesso!";
    } else {
        echo "Erro ao mover o arquivo.";
    }
}
else{
    $sql = "UPDATE usuarios
            SET foto = :foto
            WHERE id_usuario = :id_usuario";
    $stmtUpdate = $pdo->prepare($sql);

    $stmtUpdate->execute([
        ':foto' => $pasta . "placeholder.png",
        ':id_usuario' => $_SESSION['id_usuario']
    ]);

    foreach ($tiposPermitidos as $extensao) {
        $caminho = $pasta . $_SESSION["id_usuario"] . "." . $extensao;
        if (file_exists($caminho)) {
            unlink($caminho);
        }
    }
}

header("Location: ../pages/perfil.php");

?>