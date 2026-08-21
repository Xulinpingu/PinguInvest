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
$novoValor = (float) ($_POST['valor_atual'] ?? 0);

if ($idAtivo <= 0 || $novoValor <= 0) {
    die("Dados inválidos.");
}

function RetornoPercentual($valor_base, $valor_atual) {
    $retornoPercentual = 0;

    if ($valor_base > 0) {
        $retornoPercentual = (($valor_atual - $valor_base) / $valor_base) * 100;
    }

    return $retornoPercentual;
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

    $valorAtualAntigo = (float) $ativo['valor_atual'];

    $sqlUpdate = "
        UPDATE ativos
        SET valor_atual = :valor_atual
        WHERE id_ativo = :id_ativo
    ";

    $stmtUpdate = $pdo->prepare($sqlUpdate);

    $stmtUpdate->execute([
        ':valor_atual' => $novoValor,
        ':id_ativo' => $idAtivo
    ]);

    // Atualizar (ou criar) a valorização diária com base na variação do preço
    $variacaoPercentual = RetornoPercentual($ativo['preco_medio'], $novoValor);

    $sqlValorizacao = " SELECT valorizacao_diaria FROM valorizacao_ativos
                                WHERE id_usuario = :id_usuario AND id_ativo = :id_ativo AND data_val = CURDATE();";
    $stmtVal = $pdo->prepare($sqlValorizacao);
    $stmtVal->execute([
        ':id_usuario' => $idUser,
        ':id_ativo' => $idAtivo
    ]);

    $valDiaria = $stmtVal->fetchColumn();

    if ($valDiaria !== false) {
        $sqlValUpdate = " UPDATE valorizacao_ativos 
                    SET valorizacao_diaria = :valorizacao_diaria
                    WHERE id_usuario = :id_usuario AND id_ativo = :id_ativo AND data_val = CURDATE()";

        $stmtValUpdate = $pdo->prepare($sqlValUpdate);
        $stmtValUpdate->execute([
            ':valorizacao_diaria' => $valDiaria + $variacaoPercentual,
            ':id_usuario' => $idUser,
            ':id_ativo' => $idAtivo
        ]);
    } else {
        $sqlValInsert = " INSERT INTO valorizacao_ativos (id_usuario, id_ativo, valorizacao_diaria)
                            VALUES (:id_usuario, :id_ativo, :valorizacao_diaria)";

        $stmtValInsert = $pdo->prepare($sqlValInsert);
        $stmtValInsert->execute([
            ':id_usuario' => $idUser,
            ':id_ativo' => $idAtivo,
            ':valorizacao_diaria' => $variacaoPercentual
        ]);
    }

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

    $pdo->commit();

    header("Location: ../pages/wallet.php");
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    die("Erro: " . $e->getMessage());
}

?>
