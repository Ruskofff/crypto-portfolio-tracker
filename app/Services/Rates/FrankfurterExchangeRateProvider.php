<?php

namespace App\Services\Rates;

use App\Contracts\ExchangeRateProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Pulls daily reference rates from the free frankfurter.dev API.
 *
 * Reference rates are published once per business day, so the result is cached
 * for much longer than a crypto quote.
 */
class FrankfurterExchangeRateProvider implements ExchangeRateProvider
{
    /**
     * @throws ConnectionException|RequestException|RuntimeException
     */
    public function rate(string $from, string $to): float
    {
        $from = Str::upper($from);
        $to = Str::upper($to);

        if ($from === $to) {
            return 1.0;
        }

        return Cache::remember(
            sprintf('portfolio:rate:%s:%s', $from, $to),
            (int) config('portfolio.exchange_rate.cache_ttl'),
            fn (): float => $this->fetch($from, $to),
        );
    }

    /**
     * @throws ConnectionException|RequestException|RuntimeException
     */
    private function fetch(string $from, string $to): float
    {
        $response = Http::baseUrl(config('portfolio.exchange_rate.frankfurter.base_url'))
            ->connectTimeout(3)
            ->timeout((int) config('portfolio.exchange_rate.frankfurter.timeout'))
            ->retry([200, 1000], 0, function (Throwable $exception): bool {
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429));
            })
            ->get('/latest', [
                'base' => $from,
                'symbols' => $to,
            ]);

        $rate = $response->throw()->json('rates.'.$to);

        if (! is_numeric($rate)) {
            throw new RuntimeException("Frankfurter did not quote {$from} to {$to}.");
        }

        return (float) $rate;
    }
}
