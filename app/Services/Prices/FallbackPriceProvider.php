<?php

namespace App\Services\Prices;

use App\Contracts\PriceProvider;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Decorates a remote price provider and degrades to a local one when it fails.
 *
 * A rate limited or unreachable API must not take the portfolio down, so the
 * failure is logged and simulated prices are served instead.
 */
class FallbackPriceProvider implements PriceProvider
{
    public function __construct(
        private readonly PriceProvider $primary,
        private readonly PriceProvider $fallback,
    ) {}

    /**
     * @param  list<string>  $coingeckoIds
     * @return array<string, float>
     */
    public function pricesFor(array $coingeckoIds): array
    {
        try {
            return $this->primary->pricesFor($coingeckoIds);
        } catch (Throwable $exception) {
            Log::warning('Crypto price lookup failed, serving simulated prices.', [
                'provider' => $this->primary::class,
                'reason' => $exception->getMessage(),
            ]);

            return $this->fallback->pricesFor($coingeckoIds);
        }
    }
}
