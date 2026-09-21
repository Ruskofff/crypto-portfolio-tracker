<?php

namespace App\Services\Rates;

use App\Contracts\ExchangeRateProvider;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Converts using the fixed rate configured in `config/portfolio.php`.
 *
 * It only knows the application's own currency pair, in either direction,
 * which is enough for the portfolio and keeps the rate fully deterministic.
 */
class FixedExchangeRateProvider implements ExchangeRateProvider
{
    public function rate(string $from, string $to): float
    {
        $from = Str::upper($from);
        $to = Str::upper($to);

        if ($from === $to) {
            return 1.0;
        }

        $base = Str::upper(config('portfolio.currencies.base'));
        $quote = Str::upper(config('portfolio.currencies.quote'));
        $rate = (float) config('portfolio.exchange_rate.fixed');

        if ($from === $base && $to === $quote) {
            return $rate;
        }

        if ($from === $quote && $to === $base) {
            return 1 / $rate;
        }

        throw new InvalidArgumentException(
            "No fixed rate is configured for {$from} to {$to}."
        );
    }
}
