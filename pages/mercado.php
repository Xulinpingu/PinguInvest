<?php
date_default_timezone_set('America/Sao_Paulo');
session_start();

require_once __DIR__ . "/../actions/check_logado.php";
require_once "../config/hgbrasil.php";

$mercado = hgbrasil_mercado();
$grupos = $mercado['grupos'];
$taxas = $mercado['taxas'];

function mercado_numero($value, int $decimals = 2): string
{
    if (!is_numeric($value)) {
        return '—';
    }
    return number_format((float) $value, $decimals, ',', '.');
}

function mercado_preco(array $item): string
{
    $value = $item['quote']['value'] ?? null;
    if (!is_numeric($value)) {
        return '—';
    }

    $currency = strtoupper((string) ($item['currency'] ?? ''));
    $unit = strtolower((string) ($item['unit'] ?? ''));

    if ($currency === 'BRL' || $unit === 'currency' && str_ends_with((string) ($item['symbol'] ?? ''), 'BRL')) {
        return 'R$ ' . mercado_numero($value, abs((float) $value) < 1 ? 4 : 2);
    }

    if ($currency === 'USD') {
        return 'US$ ' . mercado_numero($value, 2);
    }

    if ($currency === 'POINTS' || $unit === 'points' || ($item['kind'] ?? '') === 'index') {
        return mercado_numero($value, 2) . ' pts';
    }

    return mercado_numero($value, 2);
}

function mercado_variacao(array $item): float
{
    return is_numeric($item['quote']['change_percent'] ?? null)
        ? (float) $item['quote']['change_percent']
        : 0.0;
}

function mercado_change_class(float $change): string
{
    if ($change > 0) return 'market-positive';
    if ($change < 0) return 'market-negative';
    return 'market-neutral';
}

function mercado_symbol(array $item): string
{
    $symbol = (string) ($item['symbol'] ?? $item['ticker'] ?? '—');
    return preg_replace('/BRL$/', '', $symbol) ?: $symbol;
}

function mercado_updated_label(?int $timestamp): string
{
    if (!$timestamp) {
        return 'Sem atualização disponível';
    }
    return date('d/m/Y \à\s H:i', $timestamp);
}

function mercado_logo(array $item): ?string
{
    $logo = $item['logos']['square_small'] ?? $item['logos']['square_large'] ?? null;
    return is_string($logo) && filter_var($logo, FILTER_VALIDATE_URL) ? $logo : null;
}

function mercado_volume($value): string
{
    if (!is_numeric($value)) {
        return '—';
    }

    $value = (float) $value;
    if ($value >= 1000000000) return mercado_numero($value / 1000000000, 1) . ' bi';
    if ($value >= 1000000) return mercado_numero($value / 1000000, 1) . ' mi';
    if ($value >= 1000) return mercado_numero($value / 1000, 1) . ' mil';
    return mercado_numero($value, 0);
}

function mercado_find(array $grupos, string $ticker): ?array
{
    foreach ($grupos as $grupo) {
        foreach ($grupo['itens'] as $item) {
            if (($item['ticker'] ?? '') === $ticker) {
                return $item;
            }
        }
    }
    return null;
}

$ibov = mercado_find($grupos, 'INDEXB3:IBOV');
$dolar = mercado_find($grupos, 'FOREX:USDBRL');
$bitcoin = mercado_find($grupos, 'CRYPTO:BTCBRL');
$selic = $taxas['selic'] ?? null;
$cdi = $taxas['cdi'] ?? null;

$acoesExibidas = array_merge($grupos['acoes']['itens'] ?? [], $grupos['fiis']['itens'] ?? []);
$maiorAlta = null;
$maiorBaixa = null;
foreach ($acoesExibidas as $item) {
    $change = mercado_variacao($item);
    if ($maiorAlta === null || $change > mercado_variacao($maiorAlta)) $maiorAlta = $item;
    if ($maiorBaixa === null || $change < mercado_variacao($maiorBaixa)) $maiorBaixa = $item;
}

$statusTexto = match ($mercado['origem']) {
    'api', 'api_sem_cache' => 'Dados atualizados agora',
    'cache' => 'Dados em cache',
    'cache_antigo' => 'Exibindo último dado disponível',
    'aguardando' => 'Atualização em andamento',
    default => 'Dados indisponíveis',
};
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Visão de mercado do PinguInvest com índices, ações, FIIs, moedas, criptomoedas e taxas brasileiras.">
    <title>Mercado</title>
    <link rel="icon" type="image/png" href="../assets/images/logo/pinguin.png">

    <script src="../assets/js/theme.js"></script>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <script defer src="../assets/js/functions.js"></script>
    <script defer src="../assets/js/mercado.js"></script>
</head>
<body>
    <?php require_once "../includes/header.php"; ?>

    <main class="market-page">
        <section class="market-hero" data-reveal>
            <div class="market-hero-copy">
                <span class="market-eyebrow"><i class="ph ph-wave-sine"></i> Visão de mercado</span>
                <h1>O mercado em um só painel.</h1>
                <p>Acompanhe referências do Brasil e do exterior sem sair do PinguInvest. Os indicadores gerais vêm da HG Brasil e os ativos da B3 são fornecidos pela brapi.dev.</p>

                <div class="market-meta">
                    <span class="market-live-dot <?= $mercado['tem_dado'] ? 'is-online' : '' ?>"></span>
                    <span><?= htmlspecialchars($statusTexto) ?></span>
                    <span class="market-meta-separator">•</span>
                    <span><?= htmlspecialchars(mercado_updated_label($mercado['atualizado_em'])) ?></span>
                </div>
            </div>

            <div class="market-orbit" aria-hidden="true" data-tilt data-tilt-strength="4">
                <div class="market-orbit-core"><i class="ph ph-chart-line-up"></i></div>
                <span class="market-orbit-ring ring-one"></span>
                <span class="market-orbit-ring ring-two"></span>
                <span class="market-orbit-node node-one"></span>
                <span class="market-orbit-node node-two"></span>
                <span class="market-orbit-node node-three"></span>
            </div>
        </section>

        <?php if (!$mercado['chave_configurada']): ?>
            <div class="market-alert" role="alert">
                <i class="ph ph-key"></i>
                <div>
                    <strong>Chave da HG Brasil não configurada.</strong>
                    <span>Índices, moedas, Bitcoin, CDI e Selic podem ficar indisponíveis. Defina <code>HGBRASIL_API_KEY</code> ou preencha <code>config/hgbrasil.local.php</code>.</span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (($mercado['brapi_modo'] ?? '') === 'sandbox'): ?>
            <div class="market-alert market-alert-info" role="status">
                <i class="ph ph-info"></i>
                <div>
                    <strong>brapi.dev em modo de teste.</strong>
                    <span>Sem token da brapi, o fallback exibe apenas os tickers liberados para sandbox. Para carregar ações, FIIs e ETFs normalmente, preencha <code>config/brapi.local.php</code> ou defina <code>BRAPI_API_KEY</code>.</span>
                </div>
            </div>
        <?php elseif (!empty($mercado['erro_brapi'])): ?>
            <div class="market-alert" role="alert">
                <i class="ph ph-warning-circle"></i>
                <div>
                    <strong>Os ativos da B3 não puderam ser atualizados.</strong>
                    <span><?= htmlspecialchars($mercado['erro_brapi']) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$mercado['tem_dado']): ?>
            <div class="market-alert" role="alert">
                <i class="ph ph-warning-circle"></i>
                <div>
                    <strong>Não foi possível carregar os dados de mercado.</strong>
                    <span>Verifique as chaves das APIs e a conexão do servidor.</span>
                </div>
            </div>
        <?php endif; ?>

        <section class="market-overview" aria-label="Resumo do mercado">
            <?php
            $overviewCards = [
                ['label' => 'Ibovespa', 'item' => $ibov, 'icon' => 'ph-chart-line', 'fallback' => '—'],
                ['label' => 'Dólar', 'item' => $dolar, 'icon' => 'ph-currency-dollar', 'fallback' => '—'],
                ['label' => 'Bitcoin', 'item' => $bitcoin, 'icon' => 'ph-currency-btc', 'fallback' => '—'],
            ];
            foreach ($overviewCards as $card):
                $item = $card['item'];
                $change = $item ? mercado_variacao($item) : 0;
            ?>
                <article class="market-kpi" data-reveal data-tilt>
                    <div class="market-kpi-top">
                        <span class="market-kpi-icon"><i class="ph <?= $card['icon'] ?>"></i></span>
                        <span class="market-kpi-label"><?= htmlspecialchars($card['label']) ?></span>
                    </div>
                    <strong><?= $item ? htmlspecialchars(mercado_preco($item)) : $card['fallback'] ?></strong>
                    <?php if ($item): ?>
                        <span class="market-change-pill <?= mercado_change_class($change) ?>">
                            <i class="ph <?= $change >= 0 ? 'ph-trend-up' : 'ph-trend-down' ?>"></i>
                            <?= ($change > 0 ? '+' : '') . mercado_numero($change, 2) ?>%
                        </span>
                    <?php else: ?>
                        <span class="market-change-pill market-neutral">Indisponível</span>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>

            <article class="market-kpi rates-kpi" data-reveal data-tilt>
                <div class="market-kpi-top">
                    <span class="market-kpi-icon"><i class="ph ph-bank"></i></span>
                    <span class="market-kpi-label">Juros Brasil</span>
                </div>
                <div class="market-rate-values">
                    <strong><?= is_numeric($selic) ? mercado_numero($selic, 2) . '%' : '—' ?><small> Selic</small></strong>
                    <strong><?= is_numeric($cdi) ? mercado_numero($cdi, 2) . '%' : '—' ?><small> CDI</small></strong>
                </div>
                <span class="market-kpi-note">Taxas de referência</span>
            </article>
        </section>

        <?php if ($maiorAlta || $maiorBaixa): ?>
            <section class="market-highlights" data-reveal>
                <div class="market-section-heading compact">
                    <div>
                        <span class="market-eyebrow">Movimentos</span>
                        <h2>Destaques entre os ativos exibidos</h2>
                    </div>
                    <span class="market-heading-note">Não representa todo o mercado</span>
                </div>

                <div class="market-highlight-grid">
                    <?php foreach ([['type' => 'Alta', 'item' => $maiorAlta], ['type' => 'Baixa', 'item' => $maiorBaixa]] as $highlight):
                        $item = $highlight['item'];
                        if (!$item) continue;
                        $change = mercado_variacao($item);
                    ?>
                        <article class="market-highlight-card <?= mercado_change_class($change) ?>" data-tilt>
                            <span><?= $highlight['type'] === 'Alta' ? 'Maior alta' : 'Maior baixa' ?></span>
                            <div>
                                <strong><?= htmlspecialchars(mercado_symbol($item)) ?></strong>
                                <small><?= htmlspecialchars($item['name'] ?? '') ?></small>
                            </div>
                            <b><?= ($change > 0 ? '+' : '') . mercado_numero($change, 2) ?>%</b>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="market-board" data-reveal>
            <div class="market-section-heading">
                <div>
                    <span class="market-eyebrow">Cotações</span>
                    <h2>Explore o mercado</h2>
                    <p>Filtre localmente os dados já carregados. Isso não gera novas chamadas à API.</p>
                </div>

                <div class="market-search-wrap">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="search" id="market-search" placeholder="Buscar PETR4, dólar, Bitcoin..." autocomplete="off" aria-label="Buscar ativos">
                    <kbd>Ctrl K</kbd>
                </div>
            </div>

            <div class="market-filter-bar" role="tablist" aria-label="Filtrar tipo de ativo">
                <button class="market-filter active" type="button" data-market-filter="all" role="tab" aria-selected="true">Todos</button>
                <?php foreach ($grupos as $key => $grupo): ?>
                    <button class="market-filter" type="button" data-market-filter="<?= htmlspecialchars($key) ?>" role="tab" aria-selected="false">
                        <i class="ph <?= htmlspecialchars($grupo['icon']) ?>"></i>
                        <?= htmlspecialchars($grupo['label']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="market-groups" id="market-groups">
                <?php foreach ($grupos as $key => $grupo): ?>
                    <section class="market-group" data-market-group="<?= htmlspecialchars($key) ?>">
                        <div class="market-group-heading">
                            <div>
                                <span class="market-group-icon"><i class="ph <?= htmlspecialchars($grupo['icon']) ?>"></i></span>
                                <div>
                                    <h3><?= htmlspecialchars($grupo['label']) ?></h3>
                                    <p><?= htmlspecialchars($grupo['description']) ?></p>
                                </div>
                            </div>
                            <span><?= count($grupo['itens']) ?> ativos</span>
                        </div>

                        <?php if (empty($grupo['itens'])): ?>
                            <div class="market-empty-group">Dados deste grupo não disponíveis no momento.</div>
                        <?php else: ?>
                            <div class="market-table" role="table" aria-label="Cotações de <?= htmlspecialchars($grupo['label']) ?>">
                                <div class="market-table-head" role="row">
                                    <span>Ativo</span>
                                    <span>Preço</span>
                                    <span>Variação</span>
                                    <span>Máx. / Mín.</span>
                                    <span>Volume</span>
                                    <span>Mercado</span>
                                </div>

                                <div class="market-table-body">
                                    <?php foreach ($grupo['itens'] as $item):
                                        $change = mercado_variacao($item);
                                        $logo = mercado_logo($item);
                                        $quote = $item['quote'] ?? [];
                                        $market = $item['market'] ?? [];
                                        $searchText = strtolower(implode(' ', [
                                            $item['symbol'] ?? '',
                                            $item['ticker'] ?? '',
                                            $item['name'] ?? '',
                                            $item['full_name'] ?? '',
                                            $item['classification']['sector'] ?? '',
                                        ]));
                                    ?>
                                        <article
                                            class="market-row"
                                            role="row"
                                            data-market-item
                                            data-search="<?= htmlspecialchars($searchText) ?>"
                                            data-change="<?= htmlspecialchars((string) $change) ?>"
                                            data-tilt
                                            data-tilt-strength="2"
                                        >
                                            <div class="market-asset-cell" role="cell">
                                                <div class="market-asset-logo">
                                                    <?php if ($logo): ?>
                                                        <img src="<?= htmlspecialchars($logo) ?>" alt="" loading="lazy" referrerpolicy="no-referrer">
                                                    <?php else: ?>
                                                        <span><?= htmlspecialchars(substr(mercado_symbol($item), 0, 2)) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars(mercado_symbol($item)) ?></strong>
                                                    <span title="<?= htmlspecialchars($item['full_name'] ?? $item['name'] ?? '') ?>"><?= htmlspecialchars($item['name'] ?? 'Ativo') ?></span>
                                                </div>
                                            </div>

                                            <div class="market-value-cell" role="cell" data-label="Preço">
                                                <strong><?= htmlspecialchars(mercado_preco($item)) ?></strong>
                                                <?php if (isset($quote['change_value']) && is_numeric($quote['change_value'])): ?>
                                                    <span class="<?= mercado_change_class((float) $quote['change_value']) ?>">
                                                        <?= ((float) $quote['change_value'] > 0 ? '+' : '') . mercado_numero($quote['change_value'], 2) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <div role="cell" data-label="Variação">
                                                <span class="market-change-pill <?= mercado_change_class($change) ?>">
                                                    <i class="ph <?= $change >= 0 ? 'ph-trend-up' : 'ph-trend-down' ?>"></i>
                                                    <?= ($change > 0 ? '+' : '') . mercado_numero($change, 2) ?>%
                                                </span>
                                            </div>

                                            <div class="market-range-cell" role="cell" data-label="Máx. / Mín.">
                                                <span><i class="ph ph-arrow-up"></i><?= mercado_numero($market['high'] ?? null, 2) ?></span>
                                                <span><i class="ph ph-arrow-down"></i><?= mercado_numero($market['low'] ?? null, 2) ?></span>
                                            </div>

                                            <div class="market-volume-cell" role="cell" data-label="Volume">
                                                <?= htmlspecialchars(mercado_volume($market['volume'] ?? null)) ?>
                                            </div>

                                            <div role="cell" data-label="Mercado">
                                                <?php $isOpen = $market['is_open'] ?? null; ?>
                                                <span class="market-status <?= $isOpen === true ? 'open' : ($isOpen === false ? 'closed' : '') ?>">
                                                    <span></span>
                                                    <?= $isOpen === true ? 'Aberto' : ($isOpen === false ? 'Fechado' : '—') ?>
                                                </span>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </div>

            <div class="market-no-results" id="market-no-results" hidden>
                <i class="ph ph-magnifying-glass"></i>
                <strong>Nenhum ativo encontrado</strong>
                <span>Tente buscar pelo código ou nome do ativo.</span>
            </div>
        </section>

        <section class="market-data-note" data-reveal>
            <i class="ph ph-info"></i>
            <p>
                As cotações são informativas e podem ter defasagem. O PinguInvest combina HG Brasil e brapi.dev e usa cache no servidor para reduzir o número de requisições e manter a página rápida. Não use estes dados como única base para uma decisão financeira.
            </p>
        </section>
    </main>

    <?php require_once "../includes/footer.php"; ?>
</body>
</html>
