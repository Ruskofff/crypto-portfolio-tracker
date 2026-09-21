<?php

namespace App\Contracts;

interface PriceProvider
{
    /**
     * Get the current unit price of each cryptocurrency, expressed in the
     * currency configured as `portfolio.currencies.base`.
     *
     * Identifiers the provider cannot quote are simply absent from the result,
     * so callers must not assume the two arrays have the same length.
     *
     * @param  list<string>  $coingeckoIds  CoinGecko identifiers, e.g. ['bitcoin', 'solana'].
     * @return array<string, float> Prices keyed by CoinGecko identifier.
     */
    public function pricesFor(array $coingeckoIds): array;
}
