<?php

namespace App\Services\Prices;

use App\Contracts\PriceProvider;

/**
 * Returns simulated prices without touching the network.
 *
 * Used as the `fake` driver during development and tests, and as the fallback
 * when CoinGecko is unreachable or rate limited.
 */
class FakePriceProvider implements PriceProvider
{
    /**
     * Plausible reference prices for the cryptocurrencies the demo portfolio
     * tracks, so a degraded page still reads as credible.
     *
     * @var array<string, float>
     */
    private const REFERENCE_PRICES = [
        'bitcoin' => 64250.00,
        'ethereum' => 3120.50,
        'solana' => 148.30,
        'cardano' => 0.4520,
        'ripple' => 0.5310,
        'dogecoin' => 0.1240,
    ];

    /**
     * @param  list<string>  $coingeckoIds
     * @return array<string, float>
     */
    public function pricesFor(array $coingeckoIds): array
    {
        $prices = [];

        foreach ($coingeckoIds as $coingeckoId) {
            $prices[$coingeckoId] = self::REFERENCE_PRICES[$coingeckoId]
                ?? $this->derivePrice($coingeckoId);
        }

        return $prices;
    }

    /**
     * Derive a stable price from the identifier itself, so an unknown coin is
     * quoted identically on every call.
     */
    private function derivePrice(string $coingeckoId): float
    {
        return max(0.01, round(crc32($coingeckoId) % 500_000 / 100, 2));
    }
}
