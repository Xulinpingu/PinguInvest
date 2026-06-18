<?php

require_once "../config/connDB.php";

$idUser = 1;

$tipo = trim($_POST['opt-invest'] ?? '');
$codigo = strtoupper($_POST['codigo-invest'] ?? '');
$nome = trim($_POST['nome-invest'] ?? '');
$precoM = (float) ($_POST['preco-invest'] ?? 0);
$quant = (float)($_POST['quantidade-invest'] ?? 0);

$tipoComCodigoObr = ['ACAO', 'FII', 'ETF', 'CRIPTO'];

if ( empty($tipo) || empty($nome) || $precoM <= 0 || $quant <= 0) {
    die("Dados inválidos.");
}

if (in_array($tipo, $tipoComCodigoObr) && empty($codigo)) {
    die("Código é obrigatório para este tipo de ativo.");
}

if (empty($codigo)) {
    $nomeLimpo = preg_replace('/[^a-zA-Z0-9]/', '', $nome);
    $codigo = strtoupper(substr($nomeLimpo, 0, 4));
}

try {

    $pdo->beginTransaction();

    $sql = " SELECT * FROM ativos
        WHERE id_usuario = :id_usuario AND codigo = :codigo";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id_usuario' => $idUser,
        ':codigo' => $codigo
    ]);

    $ativoExistente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ativoExistente) {
        $valorAntigo = $ativoExistente['quantidade'] * $ativoExistente['preco_medio'];

        $valorNovo = $quant * $precoM;

        $novaQuantidade = $ativoExistente['quantidade'] + $quant;

        $novoPrecoMedio = ($valorAntigo + $valorNovo) / $novaQuantidade;

        $sqlUpdate = "
            UPDATE ativos
            SET
                quantidade = :quantidade,
                preco_medio = :preco_medio
            WHERE id_ativo = :id_ativo
        ";

        $stmtUpdate = $pdo->prepare($sqlUpdate);

        $stmtUpdate->execute([
            ':quantidade' => $novaQuantidade,
            ':preco_medio' => $novoPrecoMedio,
            ':id_ativo' => $ativoExistente['id_ativo']
        ]);

        $idAtivo = $ativoExistente['id_ativo'];
    } else {
        $sql = "INSERT INTO ativos 
                (id_usuario, codigo, nome, tipo, quantidade, preco_medio, valor_atual)
                VALUES
                (:id_usuario, :codigo, :nome, :tipo, :quantidade, :preco_medio, :valor_atual)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':id_usuario' => $idUser,
            ':codigo' => $codigo,
            ':nome' => $nome,
            ':tipo' => $tipo,
            ':quantidade' => $quant,
            ':preco_medio' => $precoM,
            'valor_atual' => $precoM
        ]);

        $idAtivo = $pdo->lastInsertId();

        }
    
        $sqlMov = "INSERT INTO movimentacoes
                (id_usuario, id_ativo, tipo, quantidade, preco_unitario)
                VALUES
                (:id_usuario, :id_ativo, 'COMPRA', :quantidade, :preco)";

        $stmtMov = $pdo->prepare($sqlMov);

        $stmtMov->execute([
            ':id_usuario' => $idUser,
            ':id_ativo' => $idAtivo,
            ':quantidade' => $quant,
            ':preco' => $precoM
        ]);

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