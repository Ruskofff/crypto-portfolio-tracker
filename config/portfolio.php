<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Portfolio Currencies
    |--------------------------------------------------------------------------
    |
    | The base currency is the one crypto prices are fetched in, while the
    | quote currency is the secondary currency the portfolio is converted
    | to. Both are ISO 4217 codes and are used throughout the app.
    |
    */

    'currencies' => [
        'base' => 'USD',
        'quote' => 'EUR',
    ],

    /*
    |--------------------------------------------------------------------------
    | Price Provider
    |--------------------------------------------------------------------------
    |
    | Determines which implementation of the PriceProvider contract is bound
    | into the container. The "fake" driver returns deterministic simulated
    | prices and requires no network access, which suits tests and demos.
    |
    | Supported: "coingecko", "fake"
    |
    */

    'prices' => [

        'driver' => env('PRICE_DRIVER', 'coingecko'),

        /*
         * When the remote provider fails (network error, rate limit, 5xx), fall
         * back to simulated prices instead of letting the request blow up.
         */
        'fallback' => (bool) env('PRICE_FALLBACK', true),

        /*
         * Seconds a quote stays in the cache. Prices are recomputed on every
         * request, so this TTL only exists to stay within API rate limits.
         */
        'cache_ttl' => (int) env('PRICE_CACHE_TTL', 60),

    ],

    /*
    |--------------------------------------------------------------------------
    | CoinGecko
    |--------------------------------------------------------------------------
    |
    | The public CoinGecko API works without credentials but is rate limited.
    | Setting an API key raises those limits and is optional.
    |
    */

    'coingecko' => [
        'base_url' => env('COINGECKO_BASE_URL', 'https://api.coingecko.com/api/v3'),
        'api_key' => env('COINGECKO_API_KEY'),
        'timeout' => (int) env('COINGECKO_TIMEOUT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Exchange Rate Provider
    |--------------------------------------------------------------------------
    |
    | Controls how the base currency is converted to the quote currency. The
    | "fixed" driver uses the configurable rate below, while "frankfurter"
    | pulls daily reference rates from the free frankfurter.dev API.
    |
    | Supported: "fixed", "frankfurter"
    |
    */

    'exchange_rate' => [

        'driver' => env('EXCHANGE_RATE_DRIVER', 'fixed'),

        /*
         * When the remote provider fails, fall back to the fixed rate below
         * rather than letting the portfolio fail to render.
         */
        'fallback' => (bool) env('EXCHANGE_RATE_FALLBACK', true),

        'fixed' => (float) env('EXCHANGE_RATE_USD_EUR', 0.92),

        'cache_ttl' => (int) env('EXCHANGE_RATE_CACHE_TTL', 3600),

        'frankfurter' => [
            'base_url' => env('FRANKFURTER_BASE_URL', 'https://api.frankfurter.dev/v1'),
            'timeout' => (int) env('FRANKFURTER_TIMEOUT', 5),
        ],

    ],

];
