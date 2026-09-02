<?php

session_start();

require_once "../config/connDB.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.php");
    exit();
}
else{
    $idUser = $_SESSION['id_usuario'] ?? null;
}

$idAtivo = (int) ($_POST['id_ativo'] ?? 0);
$quantidadeVenda = (float) ($_POST['quantidade'] ?? 0);

if ($idAtivo <= 0 || $quantidadeVenda <= 0) {
    die("Dados inválidos.");
}

try {

    $pdo->beginTransaction();

    $sql = "
        SELECT * FROM ativos
        WHERE id_ativo = :id_ativo AND id_usuario = :id_usuario
        FOR UPDATE";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id_ativo' => $idAtivo,
        ':id_usuario' => $idUser
    ]);

    $ativo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ativo) {
        throw new Exception("Ativo não encontrado.");
    }

    if ($quantidadeVenda > $ativo['quantidade']) {
        throw new Exception("Quantidade maior do que a disponível em carteira.");
    }

    $novaQuantidade = $ativo['quantidade'] - $quantidadeVenda;

    $sqlUpdate = "
        UPDATE ativos
        SET quantidade = :quantidade
        WHERE id_ativo = :id_ativo
    ";

    $stmtUpdate = $pdo->prepare($sqlUpdate);

    $stmtUpdate->execute([
        ':quantidade' => $novaQuantidade,
        ':id_ativo' => $idAtivo
    ]);

    // Registrar a movimentação de venda
    $sqlMov = "INSERT INTO movimentacoes
            (id_usuario, id_ativo, tipo, quantidade, preco_unitario)
            VALUES
            (:id_usuario, :id_ativo, 'VENDA', :quantidade, :preco)";

    $stmtMov = $pdo->prepare($sqlMov);

    $stmtMov->execute([
        ':id_usuario' => $idUser,
        ':id_ativo' => $idAtivo,
        ':quantidade' => $quantidadeVenda,
        ':preco' => $ativo['valor_atual']
    ]);

    // Recalcular patrimônio total e registrar no histórico
    $sqlPatrimonio = " SELECT COALESCE(SUM(quantidade * valor_atual), 0)
                        FROM ativos
                        WHERE id_usuario = :id_usuario";

    $stmtPat = $pdo->prepare($sqlPatrimonio);

    $stmtPat->execute([
        ':id_usuario' => $idUser
    ]);

    $valorTotal = $stmtPat->fetchColumn();

    $sqlHist = " INSERT INTO historico_carteira (id_usuario, valor_total)
                    VALUES (:id_usuario, :valor_total)";

    $stmtHist = $pdo->prepare($sqlHist);

    $stmtHist->execute([
        ':id_usuario' => $idUser,
        ':valor_total' => $valorTotal
    ]);

    if ($novaQuantidade == 0) {
        $sqlDelete = "
            DELETE FROM valorizacao_ativos
            WHERE id_ativo = :id_ativo and id_usuario = :id_usuario
        ";

        $stmtDelete = $pdo->prepare($sqlDelete);

        $stmtDelete->execute([
            ':id_ativo' => $idAtivo,
            ':id_usuario' => $idUser
        ]);
    }

    $pdo->commit();

    header("Location: ../pages/wallet.php");
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    die("Erro: " . $e->getMessage());
}

?>
