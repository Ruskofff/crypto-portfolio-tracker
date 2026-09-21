<?php

namespace App\DataTransferObjects;

/**
 * One valued row of the portfolio: a quantity of one cryptocurrency held on
 * one platform, priced at the rates of the current request.
 *
 * Monetary values are rounded to the cent so that the displayed total is the
 * exact sum of the displayed rows.
 */
final readonly class PortfolioLine
{
    public function __construct(
        public int $holdingId,
        public string $coingeckoId,
        public string $cryptocurrencyName,
        public string $symbol,
        public string $platformName,
        public string $platformSlug,
        public float $quantity,
        public float $unitPrice,
        public float $value,
        public float $convertedValue,
    ) {}
}
