<?php
require_once "../config/connDB.php";

$idUser = 1;

$sql = "
    SELECT COALESCE(SUM(quantidade * preco_medio), 0)
    FROM ativos
    WHERE id_usuario = :id_usuario";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_usuario' => $idUser
]);

$patrimonioTotal = $stmt->fetchColumn();

$sqlAtivos = "
    SELECT * FROM ativos
    WHERE id_usuario = :id_usuario";

$stmtAtivos = $pdo->prepare($sqlAtivos);

$stmtAtivos->execute([
    ':id_usuario' => $idUser
]);

$ativos = $stmtAtivos->fetchAll(PDO::FETCH_ASSOC);

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
    <script defer src="../assets/js/functions.js"></script>

    <link
      rel="stylesheet"
      type="text/css"
      href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"
    />
    <link
      rel="stylesheet"
      type="text/css"
      href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"
    />
</head>
<body>
    <?php require_once "../includes/header.php" ?>

    <div class="overlay hidden"></div>
    <div class="hidden" id="add-popup">
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

    <main class="wallet-container">

        <section class="wallet-summary">

            <div class="card-circle"></div>

            <span class="wallet-label">PATRIMÔNIO TOTAL</span>

            <h1>R$ <?= number_format($patrimonioTotal, 2, ',', '.') ?></h1>

            <p class="wallet-profit">▲ 0,00% hoje</p>

            <div class="wallet-stats">  
                <div>
                    <span>INVESTIDO</span>
                    <strong>R$ 0,00</strong>
                </div>

                <div>
                    <span>RETORNO</span>
                    <strong>R$ 0,00</strong>
                </div>

                <div>
                    <span>% TOTAL</span>
                    <strong>0,00%</strong>
                </div>
            </div>
        </section>

        <section class="dashboard-grid">

            <div class="dashboard-card">
                <h3>Evolução</h3>

                <div class="placeholder-chart">
                    Gráfico em breve
                </div>
            </div>

            <div class="dashboard-card">
                <h3>Alocação</h3>

                <div class="placeholder-chart">
                    Pizza em breve
                </div>
            </div>

            <div class="dashboard-card" id="last-card">
                <h3>Seus Ativos</h3>

                <div class="ativos-lista">

                    <?php foreach($ativos as $ativo): ?>

                    <?php
                    $valorTotal = $ativo['quantidade'] * $ativo['valor_atual'];

                    $retornoPercentual = 0;

                    if($ativo['preco_medio'] > 0){
                        $retornoPercentual =
                            (($ativo['valor_atual'] - $ativo['preco_medio'])
                            / $ativo['preco_medio']) * 100;
                    }
                    ?>

                    <div class="ativo-card">

                        <div class="ativo-esquerda">

                            <div class="ativo-logo">
                                <?= substr($ativo['codigo'], 0, 4) ?>
                            </div>

                            <div class="ativo-info">

                                <div class="ativo-topo">
                                    <strong class="ativo-nome"><?= htmlspecialchars($ativo['codigo']) ?></strong>

                                    <span class="badge-tipo">
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

                            <span class="<?= $retornoPercentual >= 0 ? 'lucro' : 'prejuizo' ?>">
                                <?= $retornoPercentual >= 0 ? '+' : '' ?>
                                <?= number_format($retornoPercentual, 2, ',', '.') ?>%
                            </span>

                        </div>

                    </div>

                    <?php endforeach; ?>

                </div>
            </div>

        </section>

    </main>

    <button id="add-btn" onclick="openPopup()"><i class="ph ph-plus"></i></button>

    <?php require_once "../includes/footer.php" ?>
</body>

<script>
    const input_invest = document.querySelector("#opt-invest");
    const buttons_invest = document.querySelectorAll(".options-invest-btns button");

    const popup = document.querySelector("#add-popup");
    const popup_btn = document.querySelector("#add-btn");
    const overlay = document.querySelector(".overlay");
    
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
        popup.classList.add("hidden");
        overlay.classList.add("hidden");
    }

    function openPopup() {
        popup.classList.remove("hidden");
        overlay.classList.remove("hidden");
    }

    document.addEventListener("click", function(event) {
        if (popup_btn.contains(event.target)) {
            return;
        }

        const clickedAddPopup = popup.contains(event.target);

        if (!clickedAddPopup) {
            closePopup();
        }
    });

    const addBtn = document.getElementById("add-btn");
    const footer = document.querySelector("footer");

    window.addEventListener("scroll", () => {
        const footerTop = footer.getBoundingClientRect().top;
        const viewportHeight = window.innerHeight;

        const overlap = viewportHeight - footerTop;

        if(overlap > 0) {
            addBtn.style.transform = `translateY(-${overlap}px)`;
        } else {
            addBtn.style.transform = `translateY(0)`;
        }
    });

    function toggleTooltip(button) {
        const tooltip =
            button.parentElement.querySelector(".tooltip-text");

        tooltip.classList.toggle("active");
    }

</script>   
</html>