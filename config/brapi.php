<?php

/**
 * Integração brapi.dev — ativos da B3
 *
 * Estratégia:
 * - token somente no backend (Authorization: Bearer);
 * - usa /api/quote/list, que já retorna preço/variação/volume de vários ativos;
 * - uma resposta fica em cache por 15 minutos;
 * - se o endpoint de listagem exigir token e nenhum estiver configurado,
 *   usa os quatro tickers oficiais de sandbox para a página não quebrar.
 */

$brapiLocal = [];
$brapiLocalFile = __DIR__ . '/brapi.local.php';
if (is_file($brapiLocalFile)) {
    $loaded = require $brapiLocalFile;
    if (is_array($loaded)) {
        $brapiLocal = $loaded;
    }
}

$brapiEnvKey = getenv('BRAPI_API_KEY');
define('BRAPI_API_KEY', $brapiEnvKey !== false && $brapiEnvKey !== '' ? $brapiEnvKey : ($brapiLocal['api_key'] ?? ''));
define('BRAPI_LIST_URL', 'https://brapi.dev/api/quote/list');
define('BRAPI_QUOTE_V2_URL', 'https://brapi.dev/api/v2/stocks/quote');
define('BRAPI_CACHE_DIR', __DIR__ . '/../cache/brapi');
define('BRAPI_CACHE_TTL', 900); // 15 min
define('BRAPI_HTTP_TIMEOUT', 8);

function brapi_cache_path(string $key): string
{
    if (!is_dir(BRAPI_CACHE_DIR)) {
        @mkdir(BRAPI_CACHE_DIR, 0775, true);
    }

    $safe = preg_replace('/[^a-z0-9_-]/i', '_', $key);
    return BRAPI_CACHE_DIR . '/' . $safe . '.json';
}

function brapi_read_cache_raw(string $key): ?array
{
    $path = brapi_cache_path($key);
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

function brapi_read_cache(string $key, int $ttl = BRAPI_CACHE_TTL): ?array
{
    $cache = brapi_read_cache_raw($key);
    if ($cache === null || $cache['_age'] > $ttl) {
        return null;
    }
    return $cache;
}

function brapi_save_cache(string $key, array $data): void
{
    $payload = json_encode([
        'saved_at' => time(),
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($payload === false) {
        return;
    }

    $path = brapi_cache_path($key);
    $tmp = $path . '.tmp';

    if (@file_put_contents($tmp, $payload, LOCK_EX) !== false) {
        @rename($tmp, $path);
    }
}

/**
 * GET no backend. O token vai no header, nunca no HTML ou na URL.
 */
function brapi_http_get(string $url, array $params = []): array
{
    $requestUrl = $url;
    if ($params) {
        $requestUrl .= '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    $headers = [
        'Accept: application/json',
        'User-Agent: PinguInvest/1.0',
    ];

    if (BRAPI_API_KEY !== '') {
        $headers[] = 'Authorization: Bearer ' . BRAPI_API_KEY;
    }

    $body = false;
    $status = 0;
    $transportError = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($requestUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => BRAPI_HTTP_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'PinguInvest/1.0',
            CURLOPT_HTTPHEADER => $headers,
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
                'timeout' => BRAPI_HTTP_TIMEOUT,
                'ignore_errors' => true,
                'header' => implode("\r\n", $headers) . "\r\n",
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
            'error' => $transportError ?: 'Falha de conexão com a brapi.',
        ];
    }

    $json = json_decode($body, true);
    if (!is_array($json)) {
        return [
            'ok' => false,
            'status' => $status,
            'data' => null,
            'error' => 'Resposta inválida da brapi.',
        ];
    }

    $ok = $status >= 200 && $status < 300;

    $error = null;
    if (!$ok) {
        $error = $json['message']
            ?? $json['error']
            ?? $json['detail']
            ?? ('A brapi recusou a requisição (HTTP ' . $status . ').');

        if (is_array($error)) {
            $error = json_encode($error, JSON_UNESCAPED_UNICODE);
        }
    }

    return [
        'ok' => $ok,
        'status' => $status,
        'data' => $json,
        'error' => $error,
    ];
}

function brapi_with_cache(string $key, callable $refresh): array
{
    $fresh = brapi_read_cache($key);
    if ($fresh !== null) {
        return [
            'data' => $fresh['data'],
            'updated_at' => $fresh['saved_at'],
            'origin' => 'cache',
            'error' => null,
        ];
    }

    $lockPath = brapi_cache_path($key . '_lock');
    $lock = @fopen($lockPath, 'c+');

    if ($lock && flock($lock, LOCK_EX | LOCK_NB)) {
        $fresh = brapi_read_cache($key);
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

        $result = $refresh();

        if (!empty($result['ok'])) {
            brapi_save_cache($key, $result['data'] ?? []);
            $savedAt = time();

            flock($lock, LOCK_UN);
            fclose($lock);

            return [
                'data' => $result['data'] ?? [],
                'updated_at' => $savedAt,
                'origin' => 'api',
                'error' => null,
            ];
        }

        flock($lock, LOCK_UN);
        fclose($lock);

        $old = brapi_read_cache_raw($key);

        return [
            'data' => $old['data'] ?? [],
            'updated_at' => $old['saved_at'] ?? null,
            'origin' => $old ? 'cache_antigo' : 'indisponivel',
            'error' => $result['error'] ?? 'Falha ao atualizar a brapi.',
        ];
    }

    if ($lock) {
        fclose($lock);
        $old = brapi_read_cache_raw($key);

        return [
            'data' => $old['data'] ?? [],
            'updated_at' => $old['saved_at'] ?? null,
            'origin' => $old ? 'cache_antigo' : 'aguardando',
            'error' => $old ? null : 'Os dados da brapi estão sendo atualizados.',
        ];
    }

    $result = $refresh();

    return [
        'data' => !empty($result['ok']) ? ($result['data'] ?? []) : [],
        'updated_at' => !empty($result['ok']) ? time() : null,
        'origin' => !empty($result['ok']) ? 'api_sem_cache' : 'indisponivel',
        'error' => !empty($result['ok']) ? null : ($result['error'] ?? 'Falha ao atualizar a brapi.'),
    ];
}

function brapi_known_etfs(): array
{
    return [
        'BOVA11', 'IVVB11', 'SMAL11', 'DIVO11', 'HASH11', 'XINA11',
        'GOLD11', 'NASD11', 'SPXI11', 'WRLD11', 'PIBB11', 'BOVV11',
        'ECOO11', 'MATB11', 'FIND11', 'ISUS11',
    ];
}

function brapi_normalize_list_item(array $item): ?array
{
    $symbol = strtoupper(trim((string) ($item['stock'] ?? '')));
    if ($symbol === '') {
        return null;
    }

    $type = strtolower((string) ($item['type'] ?? 'stock'));
    $kind = ($type === 'etf' || in_array($symbol, brapi_known_etfs(), true))
        ? 'etf'
        : (in_array($type, ['fund', 'fii'], true) ? 'fund' : 'stock');

    return [
        'ticker' => 'B3:' . $symbol,
        'symbol' => $symbol,
        'kind' => $kind,
        'unit' => 'currency',
        'currency' => 'BRL',
        'name' => (string) ($item['name'] ?? $symbol),
        'full_name' => (string) ($item['name'] ?? $symbol),
        'quote' => [
            'value' => is_numeric($item['close'] ?? null) ? (float) $item['close'] : null,
            'change_percent' => is_numeric($item['change'] ?? null) ? (float) $item['change'] : 0.0,
            'change_value' => null,
        ],
        'market' => [
            'high' => null,
            'low' => null,
            'volume' => is_numeric($item['volume'] ?? null) ? (float) $item['volume'] : null,
            'market_cap' => is_numeric($item['market_cap'] ?? null) ? (float) $item['market_cap'] : null,
            'is_open' => null,
        ],
        'logos' => [
            'square_small' => filter_var($item['logo'] ?? null, FILTER_VALIDATE_URL) ? $item['logo'] : null,
        ],
        'classification' => [
            'sector' => $item['sector'] ?? null,
        ],
        'source' => 'brapi',
    ];
}

function brapi_normalize_v2_result(array $result): ?array
{
    $symbol = strtoupper((string) ($result['symbol'] ?? $result['requestedSymbol'] ?? ''));
    $data = $result['data'] ?? $result;

    if ($symbol === '' || !is_array($data)) {
        return null;
    }

    return [
        'ticker' => 'B3:' . $symbol,
        'symbol' => $symbol,
        'kind' => 'stock',
        'unit' => 'currency',
        'currency' => (string) ($data['currency'] ?? 'BRL'),
        'name' => (string) ($data['shortName'] ?? $symbol),
        'full_name' => (string) ($data['longName'] ?? $data['shortName'] ?? $symbol),
        'quote' => [
            'value' => is_numeric($data['regularMarketPrice'] ?? null) ? (float) $data['regularMarketPrice'] : null,
            'change_percent' => is_numeric($data['regularMarketChangePercent'] ?? null) ? (float) $data['regularMarketChangePercent'] : 0.0,
            'change_value' => is_numeric($data['regularMarketChange'] ?? null) ? (float) $data['regularMarketChange'] : null,
        ],
        'market' => [
            'high' => is_numeric($data['regularMarketDayHigh'] ?? null) ? (float) $data['regularMarketDayHigh'] : null,
            'low' => is_numeric($data['regularMarketDayLow'] ?? null) ? (float) $data['regularMarketDayLow'] : null,
            'volume' => is_numeric($data['regularMarketVolume'] ?? null) ? (float) $data['regularMarketVolume'] : null,
            'market_cap' => is_numeric($data['marketCap'] ?? null) ? (float) $data['marketCap'] : null,
            'is_open' => null,
        ],
        'logos' => [
            'square_small' => filter_var($data['logourl'] ?? null, FILTER_VALIDATE_URL) ? $data['logourl'] : null,
        ],
        'classification' => [
            'sector' => null,
        ],
        'source' => 'brapi',
    ];
}

function brapi_fetch_sandbox(): array
{
    $items = [];
    $errors = [];

    foreach (['PETR4', 'VALE3', 'ITUB4', 'MGLU3'] as $symbol) {
        $response = brapi_http_get(BRAPI_QUOTE_V2_URL, ['symbols' => $symbol]);

        if (!$response['ok']) {
            $errors[] = $response['error'];
            continue;
        }

        foreach (($response['data']['results'] ?? []) as $result) {
            if (!is_array($result)) {
                continue;
            }

            $normalized = brapi_normalize_v2_result($result);
            if ($normalized !== null) {
                $items[] = $normalized;
            }
        }
    }

    return [
        'ok' => !empty($items),
        'data' => [
            'items' => $items,
            'mode' => 'sandbox',
        ],
        'error' => $items ? null : (implode(' | ', array_unique(array_filter($errors))) ?: 'A brapi não retornou os ativos de teste.'),
    ];
}

function brapi_fetch_market_list(): array
{
    return brapi_with_cache('market_list', static function (): array {
        $response = brapi_http_get(BRAPI_LIST_URL, [
            'limit' => 140,
            'sortBy' => 'volume',
            'sortOrder' => 'desc',
        ]);

        if ($response['ok'] && !empty($response['data']['stocks']) && is_array($response['data']['stocks'])) {
            $items = [];

            foreach ($response['data']['stocks'] as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $normalized = brapi_normalize_list_item($item);
                if ($normalized !== null) {
                    $items[] = $normalized;
                }
            }

            if ($items) {
                return [
                    'ok' => true,
                    'data' => [
                        'items' => $items,
                        'mode' => BRAPI_API_KEY !== '' ? 'authenticated' : 'public_list',
                    ],
                    'error' => null,
                ];
            }
        }

        // Sem token (ou em uma mudança de regra do endpoint de lista),
        // mantém a página útil com os quatro símbolos liberados para teste.
        if (BRAPI_API_KEY === '') {
            return brapi_fetch_sandbox();
        }

        return [
            'ok' => false,
            'data' => [],
            'error' => $response['error'] ?? 'Não foi possível carregar os ativos da B3.',
        ];
    });
}

function brapi_market_groups(): array
{
    $response = brapi_fetch_market_list();
    $data = is_array($response['data']) ? $response['data'] : [];
    $items = is_array($data['items'] ?? null) ? $data['items'] : [];

    $stocks = [];
    $funds = [];
    $etfs = [];

    foreach ($items as $item) {
        $kind = $item['kind'] ?? 'stock';

        if ($kind === 'etf') {
            $etfs[] = $item;
        } elseif ($kind === 'fund') {
            $funds[] = $item;
        } elseif ($kind === 'stock') {
            $stocks[] = $item;
        }
    }

    return [
        'groups' => [
            'acoes' => array_slice($stocks, 0, 30),
            'fiis' => array_slice($funds, 0, 20),
            'etfs' => array_slice($etfs, 0, 15),
        ],
        'updated_at' => $response['updated_at'],
        'origin' => $response['origin'],
        'error' => $response['error'],
        'mode' => $data['mode'] ?? 'unavailable',
        'token_configured' => BRAPI_API_KEY !== '',
    ];
}
