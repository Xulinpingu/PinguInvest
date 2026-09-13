<?php

$idUser = $_SESSION['id_usuario'];

$assinante = false;
$admin = false;

$data_hoje = new DateTime();

if ($_SESSION['email'] == "xulinpingu.equipe@gmail.com") {
    $admin = true;
}

$sql = "SELECT *, CURRENT_TIMESTAMP AS data_hoje FROM assinaturas
        WHERE id_usuario = :id_usuario
        ORDER BY data_inicio DESC
        LIMIT 1";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_usuario' => $idUser
]);

$assinatura_info = $stmt->fetch(PDO::FETCH_ASSOC);

if ($assinatura_info != null){
    if($assinatura_info['data_hoje'] > $assinatura_info['data_fim']){
        $sql = "UPDATE assinaturas
                SET status = 'expirada'
                WHERE id_assinatura = :id_assinatura";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':id_assinatura' => $assinatura_info['id_assinatura']
        ]);
    }
    else if($assinatura_info['status'] == 'ativa'){
        $assinante = true;
    }
}