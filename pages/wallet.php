<?php

session_start();

require_once "../config/connDB.php";

$soma_percentual = 0;
$investido = 0;
$soma_valHoje = 0;

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.php");
    exit();
}
else{
    $idUser = $_SESSION['id_usuario'];
}

$sql = "
    SELECT valor_total
    FROM historico_carteira
    WHERE id_usuario = :id_usuario
    ORDER BY id_historico DESC LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_usuario' => $idUser
]);

$patrimonioTotal = $stmt->fetchColumn();

$sql = "
    SELECT SUM(quantidade * preco_unitario) as valor_venda
    FROM movimentacoes
    WHERE tipo = 'VENDA' and id_usuario = :id_usuario";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_usuario' => $idUser
]);

$valorVenda = $stmt->fetchColumn();

$sqlAtivos = "
    SELECT * FROM ativos
    WHERE id_usuario = :id_usuario";

$stmtAtivos = $pdo->prepare($sqlAtivos);

$stmtAtivos->execute([
    ':id_usuario' => $idUser
]);

$ativos = $stmtAtivos->fetchAll(PDO::FETCH_ASSOC);

// Preparar para pegar soma valorizaçãoes de cada ativo
$sqlValorizacoes = " SELECT SUM(valorizacao_diaria) FROM valorizacao_ativos
                    WHERE id_usuario = :id_usuario AND id_ativo = :id_ativo";
$stmtValorizacoes = $pdo->prepare($sqlValorizacoes);

// Preparar para pegar soma valorizaçãoes de hoje
$sqlValorizacaoHoje = " SELECT SUM(valorizacao_diaria) FROM valorizacao_ativos
                        WHERE id_usuario = :id_usuario AND data_val = CURDATE()";
$stmtValorizacaoHoje = $pdo->prepare($sqlValorizacaoHoje);
$stmtValorizacaoHoje->execute([
    ':id_usuario' => $idUser
]);
$valorizacaoHoje = $stmtValorizacaoHoje->fetchColumn();

$sql = "SELECT (quantidade * preco_unitario) AS multiRow FROM movimentacoes WHERE tipo = 'COMPRA'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Graficos

$sql = "
    SELECT 
        h.valor_total,
        h.registrado_em
    FROM historico_carteira h
    INNER JOIN (
        SELECT 
            id_usuario,
            YEAR(registrado_em) AS ano,
            MONTH(registrado_em) AS mes,
            MAX(registrado_em) AS ultima_data
        FROM historico_carteira
        WHERE id_usuario = :id_usuario
        GROUP BY 
            id_usuario,
            YEAR(registrado_em),
            MONTH(registrado_em)
    ) ultimos
        ON h.id_usuario = ultimos.id_usuario
        AND h.registrado_em = ultimos.ultima_data
    WHERE h.id_usuario = :id_usuario
    ORDER BY h.registrado_em
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_usuario' => $idUser
]);

$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 

$sqlAlocacao = "
    SELECT
        tipo,
        SUM(quantidade * valor_atual) AS valor_total
    FROM ativos
    WHERE id_usuario = :id_usuario
    GROUP BY tipo
    ORDER BY valor_total DESC
";

$stmtAlocacao = $pdo->prepare($sqlAlocacao);

$stmtAlocacao->execute([
    ':id_usuario' => $idUser
]);

$alocacao = $stmtAlocacao->fetchAll(PDO::FETCH_ASSOC);

$labelsAlocacao = [];
$valoresAlocacao = [];

foreach ($alocacao as $item) {
    $labelsAlocacao[] = str_replace('_', ' ', $item['tipo']);
    $valoresAlocacao[] = (float) $item['valor_total'];
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carteira</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/logo/pinguin.png">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="../assets/js/functions.js"></script>

    <link
      rel="stylesheet"
      type="text/css"
      href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"
    />
</head>
<body>
    <?php require_once "../includes/header.php" ?>

    <div class="overlay hidden"></div>
    <div class="hidden popup" id="add-popup">
        <form action="../actions/add_ativos.php" method="POST">
            <div class="options-invest">
                <label for="opt-invest">Adiconar Ativo</label>
                <input type="hidden" name="opt-invest" id="opt-invest" value="ACAO">

                <div class="options-invest-btns">
                    <button type="button" class="selected-invest" onclick="selectInvestType('ACAO', this)">
                        <i></i>
                        <p>Ações</p>
                    </button>

                    <button type="button" class="" onclick="selectInvestType('FII', this)">
                        <i></i>
                        <p>FII</p>
                    </button>

                    <button type="button" class="" onclick="selectInvestType('RENDA_FIXA', this)">
                        <i></i>
                        <p>Renda Fixa</p>
                    </button>

                    <button type="button" class="" onclick="selectInvestType('ETF', this)">
                        <i></i>
                        <p>ETF</p>
                    </button>

                    <button type="button" class="" onclick="selectInvestType('CRIPTO', this)">
                        <i></i>
                        <p>Cripto</p>
                    </button>

                    <button type="button" class="" onclick="selectInvestType('OUTROS', this)">
                        <i></i>
                        <p>Outros</p>
                    </button>
                </div>
                
            </div>

            <div class="invest-inputs">  
                <div class="invest-inputs-txt">
                    <div class="lable-row">
                        <label for="codigo-invest">CÓDIGO</label>
                        <button type="button" class="tooltip-btn" onclick="toggleTooltip(this)"><i class="ph ph-question"></i></button>
                        <span class="tooltip-text">Obrigatório para Ações, FIIs, ETFs e Criptos. Para Renda Fixa e Outros o código é gerado automaticamente (mas pode ser escolido por você).</span>
                    </div>
                    <input type="text" id="codigo-invest" name="codigo-invest" placeholder="EX: PETR4, BTC, HGLG11">
                </div>

                <div class="invest-inputs-txt">
                    <label for="nome-invest">NOME</label>
                    <input type="text" id="nome-invest" name="nome-invest" placeholder="EX: CDB Itau, SpaceX" required>
                </div>

                <div class="invest-inputs-num">
                    <div>
                        <label for="preco-invest">PREÇO (R$)</label>
                        <input type="number"step="0.01" min="0" id="preco-invest" name="preco-invest" placeholder="R$ 0,00" required>
                    </div>

                    <div>
                        <label for="quantidade-invest">QUANTIDADE</label>
                        <input type="number" step="0.01" min="0" id="quantidade-invest" name="quantidade-invest" placeholder="0" required>
                    </div>
                </div>
            </div>

            <div class="invest-btns">
                <button type="submit" class="confirm-btn">Adicionar à Carteira</button>
                <button type="button" class="cancel-btn" onclick="closePopup()">Cancelar</button>
            </div>
        </form>

    </div>

    <div class="hidden popup" id="sell-popup">

        <div class="popup-header">
            <h2>Vender Ativo</h2>
            <p class="popup-subtitle">Informe a quantidade que deseja remover de cada ativo</p>
        </div>

        <div class="popup-lista">

            <?php foreach($ativos as $ativo): ?>
            <?php if($ativo['quantidade'] <= 0) continue;?>

            <form class="ativo-card" action="../actions/sell.php" method="POST" data-valor-atual="<?= htmlspecialchars($ativo['valor_atual']) ?>">

                <input type="hidden" name="id_ativo" value="<?= $ativo['id_ativo'] ?>">

                <div class="ativo-esquerda">

                    <div class="ativo-logo <?= strtolower($ativo['tipo']) ?>">
                        <?= substr($ativo['codigo'], 0, 4) ?>
                    </div>

                    <div class="ativo-info">

                        <div class="ativo-topo">
                            <strong class="ativo-nome"><?= htmlspecialchars($ativo['codigo']) ?></strong>

                            <span class="badge-tipo <?= strtolower($ativo['tipo']) ?>">
                                <?= str_replace('_', ' ', $ativo['tipo']) ?>
                            </span>
                        </div>

                        <span class="ativo-detalhes">
                            <?= number_format($ativo['quantidade'], 2, ',', '.') ?> un <br>
                            R$ <?= number_format($ativo['valor_atual'], 2, ',', '.') ?>
                        </span>

                    </div>

                </div>

                <div class="ativo-acao">
                    <input type="number" step="0.01" min="0.01" max="<?= $ativo['quantidade'] ?>" name="quantidade" placeholder="Qtd" required>
                    <button type="submit" class="ativo-acao-btn sell-action" title="Vender">
                        <i class="ph ph-minus"></i>
                    </button>
                </div>
            </form>

            <?php endforeach; ?>
            <div class="cancel-div">
                <button type="button" class="cancel-btn" onclick="closePopup()">Cancelar</button>
            </div>
        </div>

    </div>

    <div class="hidden popup" id="edit-popup">

        <div class="popup-header">
            <h2>Editar Valor Atual</h2>
            <p class="popup-subtitle">Atualize a cotação atual de cada ativo</p>
        </div>

        <div class="popup-lista">

            <?php foreach($ativos as $ativo): ?>
            <?php if($ativo['quantidade'] <= 0) continue;?>

            <form class="ativo-card" action="../actions/edit.php" method="POST">

                <input type="hidden" name="id_ativo" value="<?= $ativo['id_ativo'] ?>">

                <div class="ativo-esquerda">

                    <div class="ativo-logo <?= strtolower($ativo['tipo']) ?>">
                        <?= substr($ativo['codigo'], 0, 4) ?>
                    </div>

                    <div class="ativo-info">

                        <div class="ativo-topo">
                            <strong class="ativo-nome"><?= htmlspecialchars($ativo['codigo']) ?></strong>

                            <span class="badge-tipo <?= strtolower($ativo['tipo']) ?>">
                                <?= str_replace('_', ' ', $ativo['tipo']) ?>
                            </span>
                        </div>

                        <span class="ativo-detalhes">
                            <?= number_format($ativo['quantidade'], 2, ',', '.') ?> un <br>
                            R$ <?= number_format($ativo['preco_medio'], 2, ',', '.') ?>
                        </span>

                    </div>

                </div>

                <div class="ativo-acao">
                    <input type="number" step="0.01" min="0.01" name="valor_atual" value="<?= $ativo['valor_atual'] ?>" required>
                    <button type="submit" class="ativo-acao-btn edit-action" title="Salvar">
                        <i class="ph ph-check"></i>
                    </button>
                </div>
            </form>

            <?php endforeach; ?>
            <div class="cancel-div">
                <button type="button" class="cancel-btn" onclick="closePopup()">Cancelar</button>
            </div>
        </div>
    </div>
    

    <main class="wallet-container">

        <section class="wallet-summary">

            <div class="card-circle"></div>

            <span class="wallet-label">PATRIMÔNIO TOTAL</span>

            <h1>R$ <?= number_format($patrimonioTotal, 2, ',', '.') ?></h1>

            <?php 

            foreach($ativos as $ativo){
                $stmtValorizacoes->execute([
                        ':id_usuario' => $idUser,
                        ':id_ativo' => $ativo['id_ativo']
                    ]);
                $valorizacao = $stmtValorizacoes->fetchColumn();
                
                $soma_percentual += $valorizacao;
            }

            
            foreach ($movimentacoes as $move) {
                $investido += $move['multiRow'];
            }

            ?>

            <p class="wallet wallet-<?= round($valorizacaoHoje, 2) > 0 ? 'profit' : '' ?> wallet-<?= round($valorizacaoHoje, 2) < 0 ? 'loss' : '' ?> wallet-<?= round($valorizacaoHoje, 2) == 0 ? 'neutral' : '' ?>">
                <?= round($valorizacaoHoje, 2) > 0 ? '▲' . round($valorizacaoHoje, 2) : "" ?>
                <?= round($valorizacaoHoje, 2) < 0 ? '▼' . round($valorizacaoHoje, 2) * (-1) : "" ?>
                <?= round($valorizacaoHoje, 2) == 0 ? '0.00' : "" ?>
                % hoje
            </p>

            <div class="wallet-stats">  
                <div>
                    <span>INVESTIDO</span>
                    <strong>R$ <?= round($investido, 2) ?></strong>
                </div>

                <div>
                    <span>RETORNO</span>
                    <strong>R$ <?= (round($patrimonioTotal, 2) + round($valorVenda, 2) - round($investido, 2)) ?></strong>
                </div>

                <div>
                    <span>% TOTAL</span>
                    <strong><?= round($soma_percentual, 2) ?>%</strong>
                </div>
            </div>

        </section>

        <section class="dashboard-grid">

            <div class="dashboard-card">
                <h3>Evolução</h3>

                <?php if (empty($historico)): ?>

                    <div class="placeholder-chart">
                        SEM DADOS
                    </div>

                <?php else: ?>

                    <canvas id="line-chart"></canvas>

                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <h3>Alocação</h3>

                <?php if (empty($alocacao)): ?>

                    <div class="placeholder-chart">
                        SEM DADOS
                    </div>

                <?php else: ?>

                    <canvas id="doughnut-chart"></canvas>

                <?php endif; ?>
            </div>

            <div class="dashboard-card" id="last-card">
                <h3>Seus Ativos</h3>

                <div class="ativos-lista">

                    <?php foreach($ativos as $ativo): ?>
                    <?php if($ativo['quantidade'] <= 0) continue;?>
                    <?php
                        $stmtValorizacoes->execute([
                            ':id_usuario' => $idUser,
                            ':id_ativo' => $ativo['id_ativo']
                        ]);
                        $valorizacao = $stmtValorizacoes->fetchColumn();

                        $retornoPercentual = $valorizacao;
                        $valorTotal = $ativo['quantidade'] * $ativo['valor_atual'];

                    ?>

                    <div class="ativo-card">

                        <div class="ativo-esquerda">

                            <div class="ativo-logo <?= strtolower($ativo['tipo']) ?>">
                                <?= substr($ativo['codigo'], 0, 4) ?>
                            </div>

                            <div class="ativo-info">

                                <div class="ativo-topo">
                                    <strong class="ativo-nome"><?= htmlspecialchars($ativo['codigo']) ?></strong>

                                    <span class="badge-tipo <?= strtolower($ativo['tipo']) ?>">
                                        <?= str_replace('_', ' ', $ativo['tipo']) ?>
                                    </span>
                                </div>

                                <span class="ativo-detalhes">
                                    <?= number_format($ativo['quantidade'], 2, ',', '.') ?> un <br>
                                    PM R$ <?= number_format($ativo['preco_medio'], 2, ',', '.') ?>
                                </span>

                            </div>

                        </div>

                        <div class="ativo-direita">

                            <strong class="ativo-valor">
                                R$ <?= number_format($valorTotal, 2, ',', '.') ?>
                            </strong>

                            <span class="<?= $retornoPercentual > 0 ? 'lucro' : ''?> <?= $retornoPercentual == 0 ? 'neutro' : ''?> <?= $retornoPercentual < 0 ? 'prejuizo' : '' ?>">
                                <?= $retornoPercentual > 0 ? '+' : ''?>
                                <?= number_format($retornoPercentual, 2, ',', '.') ?>%
                            </span>

                        </div>

                    </div>

                    <?php endforeach; ?>

                </div>
            </div>

        </section>

    </main>

    <div class="fab-container">
        <div class="func-anchored hidden-func">
            <button class="func-btn" id="edit-btn" onclick="openPopup('#edit-popup')"><i class="ph ph-note-pencil"></i></button>
            <button class="func-btn" id="sell-btn" onclick="openPopup('#sell-popup')"><i class="ph ph-trend-down"></i></button>
            <button class="func-btn" id="add-btn" onclick="openPopup('#add-popup')"><i class="ph ph-trend-up"></i></button>
        </div>

        <button class="func-btn" id="dots-btn" onclick="ToggleHiddenfunc()"><i class="ph ph-dots-three-outline"></i></button>
    </div>

    <?php require_once "../includes/footer.php" ?>

    <div class="confirm-overlay hidden" id="confirm-overlay">
        <div class="confirm-popup">

            <h3 id="confirm-title">Confirmar ação</h3>

            <div class="confirm-details" id="confirm-details"></div>

            <p id="confirm-message"></p>

            <div class="confirm-buttons">
                <button type="button" class="cancel-btn" id="confirm-no">
                    Cancelar
                </button>

                <button type="button" class="confirm-btn" id="confirm-yes">
                    Confirmar
                </button>
            </div>

        </div>
    </div>

</body>

<script>
    const input_invest = document.querySelector("#opt-invest");
    const buttons_invest = document.querySelectorAll(".options-invest-btns button");

    const popups = document.querySelectorAll("[id$='-popup']");
    const overlay = document.querySelector(".overlay");
    const fabButtons = document.querySelectorAll(".func-btn");

    const dots_btn = document.querySelector("#dots-btn");
    const func_div = document.querySelector(".func-anchored");
    const fab_container = document.querySelector(".fab-container");
    const footer = document.querySelector("footer");

    function selectInvestType(type, button) {
        input_invest.value = type;

        buttons_invest.forEach((btn) => {
            btn.classList.remove("selected-invest");
        });
        button.classList.add("selected-invest");

        const codigoInput = document.getElementById("codigo-invest")

        if(type === "ACAO" || type === "FII" || type === "ETF" || type === "CRIPTO"){
            codigoInput.required = true;
        }else{
            codigoInput.required = false;
        }
    }

    function closePopup() {
        popups.forEach((popup) => {
            popup.classList.add("hidden");
        });
        overlay.classList.add("hidden");
    }

    function openPopup(targetPopupId = "#add-popup") {
        const targetPopup = document.querySelector(targetPopupId);

        if (!targetPopup) {
            return;
        }

        popups.forEach((popup) => {
            popup.classList.add("hidden");
        });

        targetPopup.classList.remove("hidden");
        overlay.classList.remove("hidden");
    }

    function ToggleHiddenfunc(){
        func_div.classList.toggle("hidden-func")
    }

    document.addEventListener("click", function(event) {
        if (event.target.closest(".func-btn")) {
            return;
        }

        const activePopup = document.querySelector("[id$='-popup']:not(.hidden)");

        if (activePopup && !activePopup.contains(event.target)) {
            closePopup();
        }

        func_div.classList.add("hidden-func")
    });

    window.addEventListener("scroll", () => {
        const footerTop = footer.getBoundingClientRect().top;
        const viewportHeight = window.innerHeight;

        const overlap = viewportHeight - footerTop;

        if (overlap > 0) {
            fab_container.style.transform = `translateY(-${(overlap)}px)`;
        } else {
            fab_container.style.transform = "translateY(0)";
        }
    });

    function toggleTooltip(button) {
        const tooltip =
            button.parentElement.querySelector(".tooltip-text");

        tooltip.classList.toggle("active");
    }

    // =====================
    // GRÁFICOS
    // =====================

    // GRÁFICO DE EVOLUÇÃO
    const historico = <?= json_encode($historico) ?>;
    const ctxLine = document.getElementById("line-chart");

    if (ctxLine && historico.length > 0) {

        const labels = historico.map(item => {
            const data = new Date(item.registrado_em);

            return data.toLocaleDateString('pt-BR', {
                month: 'short',
                year: 'numeric'
            });
        });

        const valores = historico.map(item => {
            return Number(item.valor_total);
        });

        new Chart(ctxLine, {
            type: 'line',

            data: {
                labels: labels,

                datasets: [{
                    label: 'Patrimônio',
                    data: valores,
                    fill: false,
                    borderColor: 'rgb(46, 212, 122)',
                    tension: 0.1
                }]
            },

            options: {
                responsive: true,

                scales: {
                    x: {
                        ticks: {
                            color: '#F5F3FA'
                        }
                    },

                    y: {
                        beginAtZero: false,

                        ticks: {
                            color: '#F5F3FA'
                        }
                    }
                },

                plugins: {
                    legend: {
                        labels: {
                            color: '#F5F3FA'
                        }
                    }
                }
            }
        });
    }


    // =====================
    // GRÁFICO DE ALOCAÇÃO
    // =====================

    const labelsAlocacao = <?= json_encode($labelsAlocacao) ?>;
    const valoresAlocacao = <?= json_encode($valoresAlocacao) ?>;

    const ctxDoughnut = document.getElementById("doughnut-chart");

    if (ctxDoughnut && valoresAlocacao.length > 0) {

        const coresPorTipo = {
            'ACAO': '#9b5cff',
            'FII': '#ff4d5f',
            'ETF': '#5db7ff',
            'CRIPTO': '#ffb020',
            'RENDA FIXA': '#2ee66b',
            'OUTROS': '#d4d4d8'
        };

        const coresAlocacao = labelsAlocacao.map(tipo => {
            return coresPorTipo[tipo];
        });

        new Chart(ctxDoughnut, {
            type: 'doughnut',

            data: {
                labels: labelsAlocacao,

                datasets: [{
                    data: valoresAlocacao,
                    backgroundColor: coresAlocacao,
                    borderWidth: 0
                }]
            },

            options: {
                responsive: true,

                plugins: {
                    legend: {
                        labels: {
                            color: '#F5F3FA'
                        }
                    }
                }
            }
        });
    }


    const confirmOverlay = document.querySelector("#confirm-overlay");
    const confirmTitle = document.querySelector("#confirm-title");
    const confirmMessage = document.querySelector("#confirm-message");
    const confirmDetails = document.querySelector("#confirm-details");

    const confirmYes = document.querySelector("#confirm-yes");
    const confirmNo = document.querySelector("#confirm-no");

    let formParaConfirmar = null;

    document.querySelectorAll("#sell-popup form, #edit-popup form").forEach(form => {

        form.addEventListener("submit", function(event) {

            event.preventDefault();

            formParaConfirmar = this;

            const isVenda = this.action.includes("sell.php");

            // Pega informações do card
            const codigo = this.querySelector(".ativo-nome").textContent.trim();

            if (isVenda) {

                const quantidade = this.querySelector(
                    'input[name="quantidade"]'
                ).value;

                const valorAtual = this.querySelector(
                    ".ativo-detalhes"
                ).textContent.trim().split("R$")[1];

                confirmTitle.textContent = "Confirmar venda";

                confirmDetails.innerHTML = `
                    <div>
                        <span>Ativo</span>
                        <strong>${codigo}</strong>
                    </div>

                    <div>
                        <span>Quantidade</span>
                        <strong>${quantidade} unidades</strong>
                    </div>

                    <div>
                        <span>Valor atual</span>
                        <strong>R$ ${valorAtual}</strong>
                    </div>
                `;

                confirmMessage.textContent =
                    "Essa quantidade será removida da sua carteira.";

            } else {

                const valorAtual = this.querySelector(
                    'input[name="valor_atual"]'
                );

                const valorAnterior = Number(
                    this.closest(".ativo-card").dataset.valorAtual
                );

                confirmTitle.textContent = "Confirmar alteração";

                confirmDetails.innerHTML = `
                    <div>
                        <span>Ativo</span>
                        <strong>${codigo}</strong>
                    </div>

                    <div>
                        <span>Valor anterior</span>
                        <strong>R$ ${valorAnterior}</strong>
                    </div>

                    <div>
                        <span>Novo valor</span>
                        <strong>R$ ${Number(valorAtual.value).toFixed(2).replace(".", ",")}</strong>
                    </div>
                `;

                confirmMessage.textContent =
                    "O valor atual deste ativo será atualizado.";
            }

            confirmOverlay.classList.remove("hidden");
        });
    });


    confirmYes.addEventListener("click", function() {

        if (formParaConfirmar) {
            formParaConfirmar.submit();
        }

        formParaConfirmar = null;
        confirmOverlay.classList.add("hidden");
    });


    confirmNo.addEventListener("click", function() {

        formParaConfirmar = null;
        confirmOverlay.classList.add("hidden");
    });

</script>   
</html>