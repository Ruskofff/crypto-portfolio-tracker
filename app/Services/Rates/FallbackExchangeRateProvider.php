<?php

namespace App\Services\Rates;

use App\Contracts\ExchangeRateProvider;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Decorates a remote rate provider and degrades to a local one when it fails.
 *
 * Mirrors the price provider strategy: an unavailable rate API must not take
 * the portfolio down, so the configured fixed rate is used instead.
 */
class FallbackExchangeRateProvider implements ExchangeRateProvider
{
    public function __construct(
        private readonly ExchangeRateProvider $primary,
        private readonly ExchangeRateProvider $fallback,
    ) {}

    public function rate(string $from, string $to): float
    {
        try {
            return $this->primary->rate($from, $to);
        } catch (Throwable $exception) {
            Log::warning('Exchange rate lookup failed, serving the configured fixed rate.', [
                'provider' => $this->primary::class,
                'pair' => $from.'/'.$to,
                'reason' => $exception->getMessage(),
            ]);

            return $this->fallback->rate($from, $to);
        }
    }
}
