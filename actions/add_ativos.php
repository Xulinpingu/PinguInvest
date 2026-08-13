<?php

require_once "../config/connDB.php";

$idUser = 1;

$tipo = trim($_POST['opt-invest'] ?? '');
$codigo = strtoupper($_POST['codigo-invest'] ?? '');
$nome = trim($_POST['nome-invest'] ?? '');
$precoAt = (float) ($_POST['preco-invest'] ?? 0);
$quant = (float)($_POST['quantidade-invest'] ?? 0);

$tipoComCodigoObr = ['ACAO', 'FII', 'ETF', 'CRIPTO'];

if ( empty($tipo) || empty($nome) || $precoAt <= 0 || $quant <= 0) {
    die("Dados inválidos.");
}

if (in_array($tipo, $tipoComCodigoObr) && empty($codigo)) {
    die("Código é obrigatório para este tipo de ativo.");
}

if (empty($codigo)) {
    $nomeLimpo = preg_replace('/[^a-zA-Z0-9]/', '', $nome);
    $codigo = strtoupper(substr($nomeLimpo, 0, 4));
}

function RetornoPercentual($preco_medio, $valor_atual) {
    $retornoPercentual = 0;

    if($preco_medio > 0){
        $retornoPercentual = (($valor_atual - $preco_medio) / $preco_medio) * 100;
    }   

    return $retornoPercentual;
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

        $valorNovo = $quant * $precoAt;

        $novaQuantidade = $ativoExistente['quantidade'] + $quant;

        $novoPrecoMedio = ($valorAntigo + $valorNovo) / $novaQuantidade;

        $sqlUpdate = "
            UPDATE ativos
            SET
                quantidade = :quantidade,
                preco_medio = :preco_medio,
                valor_atual = :valor_atual
            WHERE id_ativo = :id_ativo
        ";

        $stmtUpdate = $pdo->prepare($sqlUpdate);

        $stmtUpdate->execute([
            ':quantidade' => $novaQuantidade,
            ':preco_medio' => $novoPrecoMedio,
            ':valor_atual' => $precoAt,
            ':id_ativo' => $ativoExistente['id_ativo']
        ]);

        $idAtivo = $ativoExistente['id_ativo'];


        // Atualizar a valorização diária do ativo
        $retornoPercentual = RetornoPercentual($novoPrecoMedio, $precoAt);

        $sqlValorizacao = " SELECT valorizacao_diaria FROM valorizacao_ativos
                                    WHERE id_usuario = :id_usuario AND id_ativo = :id_ativo AND data_val = CURDATE();";
        $stmtVal = $pdo->prepare($sqlValorizacao);
        $stmtVal->execute([
            ':id_usuario' => $idUser,
            ':id_ativo' => $idAtivo
        ]);

        $valDiaria = $stmtVal->fetchColumn();

        $sqlValUpdate = " UPDATE valorizacao_ativos 
                        SET valorizacao_diaria = :valorizacao_diaria
                        WHERE id_usuario = :id_usuario AND id_ativo = :id_ativo AND data_val = CURDATE()";

        $stmtValUpdate = $pdo->prepare($sqlValUpdate);
        $stmtValUpdate->execute([
            ':valorizacao_diaria' => $valDiaria + $retornoPercentual,
            ':id_usuario' => $idUser,
            ':id_ativo' => $idAtivo
        ]);

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
            ':valor_atual' => $precoAt,
            ':preco_medio' => $precoAt 
        ]);

        $idAtivo = $pdo->lastInsertId();


        // Criando a valorização diária do ativo
        $sqlAtivoCriado = " SELECT preco_medio FROM ativos WHERE id_ativo = :id_ativo";

        $stmtAtivoCriado = $pdo->prepare($sqlAtivoCriado);
        $stmtAtivoCriado->execute([
            ':id_ativo' => $idAtivo
        ]);

        $precoMedio = $stmtAtivoCriado->fetchColumn();

        $retornoPercentual = RetornoPercentual($precoMedio, $precoAt);

        $sqlValInsert = " INSERT INTO valorizacao_ativos (id_usuario, id_ativo, valorizacao_diaria)
                            VALUES (:id_usuario, :id_ativo, :valorizacao_diaria)";

        $stmtValInsert = $pdo->prepare($sqlValInsert);
        $stmtValInsert->execute([
            ':id_usuario' => $idUser,
            ':id_ativo' => $idAtivo,
            ':valorizacao_diaria' => $retornoPercentual
        ]);
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
        ':preco' => $precoAt
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