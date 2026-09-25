<?php

/**
 * Integração híbrida do Mercado — HG Brasil + brapi.dev
 *
 * HG Brasil:
 * - /finance em UMA chamada para índices, moedas, Bitcoin, CDI e Selic;
 * - cache de 30 minutos, compatível com a janela normal de atualização da fonte.
 *
 * brapi.dev:
 * - ações, FIIs e ETFs da B3;
 * - implementação isolada em config/brapi.php e cache próprio.
 */

require_once __DIR__ . '/brapi.php';

$hgBrasilLocal = [];
$hgBrasilLocalFile = __DIR__ . '/hgbrasil.local.php';
if (is_file($hgBrasilLocalFile)) {
    $loaded = require $hgBrasilLocalFile;
    if (is_array($loaded)) {
        $hgBrasilLocal = $loaded;
    }
}

$envKey = getenv('HGBRASIL_API_KEY');
define('HGBRASIL_API_KEY', $envKey !== false && $envKey !== '' ? $envKey : ($hgBrasilLocal['api_key'] ?? ''));
define('HGBRASIL_FINANCE_URL', 'https://api.hgbrasil.com/finance');
define('HGBRASIL_CACHE_DIR', __DIR__ . '/../cache/hgbrasil');
define('HGBRASIL_FINANCE_CACHE_TTL', 1800); // 30 min
define('HGBRASIL_HTTP_TIMEOUT', 8);

function hgbrasil_grupos_config(): array
{
    return [
        'indices' => [
            'label' => 'Índices',
            'description' => 'Termômetros dos principais mercados',
            'icon' => 'ph-chart-line-up',
        ],
        'acoes' => [
            'label' => 'Ações',
            'description' => 'Ações brasileiras com maior volume entre os dados carregados',
            'icon' => 'ph-buildings',
        ],
        'fiis' => [
            'label' => 'Fundos imobiliários',
            'description' => 'FIIs negociados na B3',
            'icon' => 'ph-buildings',
        ],
        'etfs' => [
            'label' => 'ETFs',
            'description' => 'Fundos de índice negociados na B3',
            'icon' => 'ph-chart-pie-slice',
        ],
        'moedas' => [
            'label' => 'Moedas',
            'description' => 'Cotações internacionais em relação ao real',
            'icon' => 'ph-currency-dollar',
        ],
        'criptos' => [
            'label' => 'Criptomoedas',
            'description' => 'Bitcoin cotado em real',
            'icon' => 'ph-currency-btc',
        ],
    ];
}

function hgbrasil_cache_path(string $key): string
{
    if (!is_dir(HGBRASIL_CACHE_DIR)) {
        @mkdir(HGBRASIL_CACHE_DIR, 0775, true);
    }

    $safe = preg_replace('/[^a-z0-9_-]/i', '_', $key);
    return HGBRASIL_CACHE_DIR . '/' . $safe . '.json';
}

function hgbrasil_read_cache_raw(string $key): ?array
{
    $path = hgbrasil_cache_path($key);
    if (!is_file($path)) {
        return null;
    }

    $content = @file_get_contents($path);
    if ($content === false) {
        return null;
    }

    $cache = json_decode($content, true);
    if (!is_array($cache) || !isset($cache['saved_at']) || !array_key_exists('data', $cache)) {
        return null;
    }

    $cache['_age'] = max(0, time() - (int) $cache['saved_at']);
    return $cache;
}

function hgbrasil_read_cache(string $key): ?array
{
    $cache = hgbrasil_read_cache_raw($key);
    if ($cache === null || $cache['_age'] > HGBRASIL_FINANCE_CACHE_TTL) {
        return null;
    }
    return $cache;
}

function hgbrasil_save_cache(string $key, array $data): void
{
    $payload = json_encode([
        'saved_at' => time(),
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($payload === false) {
        return;
    }

    $path = hgbrasil_cache_path($key);
    $tmp = $path . '.tmp';

    if (@file_put_contents($tmp, $payload, LOCK_EX) !== false) {
        @rename($tmp, $path);
    }
}

function hgbrasil_http_get(string $url, array $params): array
{
    if (HGBRASIL_API_KEY === '') {
        return [
            'ok' => false,
            'status' => 0,
            'data' => null,
            'error' => 'Chave HG Brasil não configurada.',
        ];
    }

    $params['key'] = HGBRASIL_API_KEY;
    $requestUrl = $url . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);

    $body = false;
    $status = 0;
    $transportError = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($requestUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => HGBRASIL_HTTP_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'PinguInvest/1.0',
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($body === false) {
            $transportError = curl_error($ch);
        }
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'timeout' => HGBRASIL_HTTP_TIMEOUT,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\nUser-Agent: PinguInvest/1.0\r\n",
            ],
        ]);

        $body = @file_get_contents($requestUrl, false, $context);

        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
            $status = (int) $match[1];
        }
    }

    if ($body === false) {
        return [
            'ok' => false,
            'status' => $status,
            'data' => null,
            'error' => $transportError ?: 'Falha de conexão com a HG Brasil.',
        ];
    }

    $json = json_decode($body, true);
    if (!is_array($json)) {
        return [
            'ok' => false,
            'status' => $status,
            'data' => null,
            'error' => 'Resposta inválida da HG Brasil.',
        ];
    }

    $ok = $status >= 200 && $status < 300 && ($json['valid_key'] ?? true) !== false;

    if (!$ok) {
        $messages = [];

        foreach (($json['errors'] ?? []) as $error) {
            if (is_array($error) && !empty($error['message'])) {
                $messages[] = (string) $error['message'];
            }
        }

        return [
            'ok' => false,
            'status' => $status,
            'data' => $json,
            'error' => $messages
                ? implode(' | ', $messages)
                : (($json['message'] ?? null) ?: 'A HG Brasil recusou a requisição.'),
        ];
    }

    return [
        'ok' => true,
        'status' => $status,
        'data' => $json,
        'error' => null,
    ];
}

function hgbrasil_fetch_finance(): array
{
    $fresh = hgbrasil_read_cache('finance_overview');

    if ($fresh !== null) {
        return [
            'data' => $fresh['data'],
            'updated_at' => $fresh['saved_at'],
            'origin' => 'cache',
            'error' => null,
        ];
    }

    $lockPath = hgbrasil_cache_path('finance_overview_lock');
    $lock = @fopen($lockPath, 'c+');

    if ($lock && flock($lock, LOCK_EX | LOCK_NB)) {
        $fresh = hgbrasil_read_cache('finance_overview');

        if ($fresh !== null) {
            flock($lock, LOCK_UN);
            fclose($lock);

            return [
                'data' => $fresh['data'],
                'updated_at' => $fresh['saved_at'],
                'origin' => 'cache',
                'error' => null,
            ];
        }

        // O endpoint básico /finance entrega moedas, índices, bitcoin e taxas de uma vez.
        $response = hgbrasil_http_get(HGBRASIL_FINANCE_URL, [
            'fields' => 'only_results,currencies,stocks,bitcoin,taxes',
        ]);

        if ($response['ok']) {
            $data = $response['data']['results'] ?? $response['data'];
            hgbrasil_save_cache('finance_overview', is_array($data) ? $data : []);
            $savedAt = time();

            flock($lock, LOCK_UN);
            fclose($lock);

            return [
                'data' => is_array($data) ? $data : [],
                'updated_at' => $savedAt,
                'origin' => 'api',
                'error' => null,
            ];
        }

        flock($lock, LOCK_UN);
        fclose($lock);

        $old = hgbrasil_read_cache_raw('finance_overview');

        return [
            'data' => $old['data'] ?? [],
            'updated_at' => $old['saved_at'] ?? null,
            'origin' => $old ? 'cache_antigo' : 'indisponivel',
            'error' => $response['error'],
        ];
    }

    if ($lock) {
        fclose($lock);
        $old = hgbrasil_read_cache_raw('finance_overview');

        return [
            'data' => $old['data'] ?? [],
            'updated_at' => $old['saved_at'] ?? null,
            'origin' => $old ? 'cache_antigo' : 'aguardando',
            'error' => $old ? null : 'Os dados da HG Brasil estão sendo atualizados.',
        ];
    }

    $response = hgbrasil_http_get(HGBRASIL_FINANCE_URL, [
        'fields' => 'only_results,currencies,stocks,bitcoin,taxes',
    ]);

    if (!$response['ok']) {
        return [
            'data' => [],
            'updated_at' => null,
            'origin' => 'indisponivel',
            'error' => $response['error'],
        ];
    }

    $data = $response['data']['results'] ?? $response['data'];

    return [
        'data' => is_array($data) ? $data : [],
        'updated_at' => time(),
        'origin' => 'api_sem_cache',
        'error' => null,
    ];
}

function hgbrasil_normalize_index(string $apiKey, array $item): array
{
    $map = [
        'IBOVESPA' => ['ticker' => 'INDEXB3:IBOV', 'symbol' => 'IBOV', 'name' => 'Ibovespa'],
        'IFIX' => ['ticker' => 'INDEXB3:IFIX', 'symbol' => 'IFIX', 'name' => 'IFIX'],
        'NASDAQ' => ['ticker' => 'INDEXNASDAQ:IXIC', 'symbol' => 'NASDAQ', 'name' => 'Nasdaq'],
        'DOWJONES' => ['ticker' => 'INDEXNYSE:DJI', 'symbol' => 'DOW', 'name' => 'Dow Jones'],
        'CAC' => ['ticker' => 'INDEXEPA:FCHI', 'symbol' => 'CAC40', 'name' => 'CAC 40'],
        'NIKKEI' => ['ticker' => 'INDEXNIKKEI:N225', 'symbol' => 'NIKKEI', 'name' => 'Nikkei 225'],
    ];

    $meta = $map[$apiKey] ?? [
        'ticker' => 'INDEX:' . $apiKey,
        'symbol' => $apiKey,
        'name' => (string) ($item['name'] ?? $apiKey),
    ];

    return [
        'ticker' => $meta['ticker'],
        'symbol' => $meta['symbol'],
        'kind' => 'index',
        'unit' => 'points',
        'currency' => 'POINTS',
        'name' => $meta['name'],
        'full_name' => (string) ($item['name'] ?? $meta['name']),
        'quote' => [
            'value' => is_numeric($item['points'] ?? null) ? (float) $item['points'] : null,
            'change_percent' => is_numeric($item['variation'] ?? null) ? (float) $item['variation'] : 0.0,
            'change_value' => null,
        ],
        'market' => [
            'high' => null,
            'low' => null,
            'volume' => null,
            'is_open' => null,
        ],
        'logos' => [],
        'classification' => [
            'sector' => null,
        ],
        'source' => 'hgbrasil',
    ];
}

function hgbrasil_normalize_currency(string $iso, array $item): array
{
    return [
        'ticker' => 'FOREX:' . strtoupper($iso) . 'BRL',
        'symbol' => strtoupper($iso) . 'BRL',
        'kind' => 'currency',
        'unit' => 'currency',
        'currency' => 'BRL',
        'name' => (string) ($item['name'] ?? strtoupper($iso)),
        'full_name' => (string) ($item['name'] ?? strtoupper($iso)),
        'quote' => [
            'value' => is_numeric($item['buy'] ?? null) ? (float) $item['buy'] : null,
            'change_percent' => is_numeric($item['variation'] ?? null) ? (float) $item['variation'] : 0.0,
            'change_value' => null,
        ],
        'market' => [
            'high' => null,
            'low' => null,
            'volume' => null,
            'is_open' => null,
        ],
        'logos' => [],
        'classification' => [
            'sector' => null,
        ],
        'source' => 'hgbrasil',
    ];
}

function hgbrasil_normalize_bitcoin(array $item): array
{
    return [
        'ticker' => 'CRYPTO:BTCBRL',
        'symbol' => 'BTCBRL',
        'kind' => 'crypto',
        'unit' => 'currency',
        'currency' => 'BRL',
        'name' => 'Bitcoin',
        'full_name' => 'Bitcoin',
        'quote' => [
            'value' => is_numeric($item['buy'] ?? null) ? (float) $item['buy'] : null,
            'change_percent' => is_numeric($item['variation'] ?? null) ? (float) $item['variation'] : 0.0,
            'change_value' => null,
        ],
        'market' => [
            'high' => null,
            'low' => null,
            'volume' => null,
            'is_open' => null,
        ],
        'logos' => [],
        'classification' => [
            'sector' => null,
        ],
        'source' => 'hgbrasil',
    ];
}

function hgbrasil_general_groups(array $data): array
{
    $indices = [];
    foreach (($data['stocks'] ?? []) as $key => $item) {
        if (is_array($item)) {
            $indices[] = hgbrasil_normalize_index((string) $key, $item);
        }
    }

    $currencies = [];
    foreach (['USD', 'EUR', 'GBP'] as $iso) {
        $item = $data['currencies'][$iso] ?? null;
        if (is_array($item)) {
            $currencies[] = hgbrasil_normalize_currency($iso, $item);
        }
    }

    $cryptos = [];
    $btc = $data['currencies']['BTC'] ?? null;
    if (is_array($btc)) {
        $cryptos[] = hgbrasil_normalize_bitcoin($btc);
    }

    return [
        'indices' => $indices,
        'moedas' => $currencies,
        'criptos' => $cryptos,
    ];
}

function hgbrasil_combine_origin(string $hgOrigin, string $brapiOrigin): string
{
    $origins = [$hgOrigin, $brapiOrigin];

    if (in_array('api', $origins, true) || in_array('api_sem_cache', $origins, true)) {
        return 'api';
    }
    if (in_array('cache_antigo', $origins, true)) {
        return 'cache_antigo';
    }
    if (in_array('cache', $origins, true)) {
        return 'cache';
    }
    if (in_array('aguardando', $origins, true)) {
        return 'aguardando';
    }
    return 'indisponivel';
}

/**
 * Ponto de entrada consumido por pages/mercado.php.
 */
function hgbrasil_mercado(): array
{
    $hg = hgbrasil_fetch_finance();
    $brapi = brapi_market_groups();

    $hgData = is_array($hg['data']) ? $hg['data'] : [];
    $hgGroups = hgbrasil_general_groups($hgData);
    $brapiGroups = $brapi['groups'] ?? [];

    $groupItems = [
        'indices' => $hgGroups['indices'] ?? [],
        'acoes' => $brapiGroups['acoes'] ?? [],
        'fiis' => $brapiGroups['fiis'] ?? [],
        'etfs' => $brapiGroups['etfs'] ?? [],
        'moedas' => $hgGroups['moedas'] ?? [],
        'criptos' => $hgGroups['criptos'] ?? [],
    ];

    $groups = [];
    foreach (hgbrasil_grupos_config() as $key => $config) {
        $groups[$key] = [
            'label' => $config['label'],
            'description' => $config['description'],
            'icon' => $config['icon'],
            'itens' => $groupItems[$key] ?? [],
        ];
    }

    $taxes = [];
    if (!empty($hgData['taxes'][0]) && is_array($hgData['taxes'][0])) {
        $taxes = $hgData['taxes'][0];
    }

    $updatedCandidates = array_values(array_filter([
        $hg['updated_at'] ?? null,
        $brapi['updated_at'] ?? null,
    ], 'is_int'));

    $itemCount = 0;
    foreach ($groups as $group) {
        $itemCount += count($group['itens']);
    }

    return [
        'grupos' => $groups,
        'taxas' => $taxes,
        'atualizado_em' => $updatedCandidates ? max($updatedCandidates) : null,
        'hg_atualizado_em' => $hg['updated_at'] ?? null,
        'brapi_atualizado_em' => $brapi['updated_at'] ?? null,
        'origem' => hgbrasil_combine_origin($hg['origin'], $brapi['origin']),
        'chave_configurada' => HGBRASIL_API_KEY !== '',
        'brapi_token_configurado' => $brapi['token_configured'] ?? false,
        'brapi_modo' => $brapi['mode'] ?? 'unavailable',
        'tem_dado' => $itemCount > 0,
        'erro' => $hg['error'],
        'erro_hg' => $hg['error'],
        'erro_brapi' => $brapi['error'] ?? null,
    ];
}
