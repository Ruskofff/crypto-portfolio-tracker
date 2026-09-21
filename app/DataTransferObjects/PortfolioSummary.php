<?php

namespace App\DataTransferObjects;

use Illuminate\Support\Carbon;

/**
 * The whole portfolio as valued for a single request: every row sorted by
 * decreasing value, plus the totals in both currencies.
 */
final readonly class PortfolioSummary
{
    /**
     * @param  list<PortfolioLine>  $lines  Sorted by decreasing value.
     */
    public function __construct(
        public array $lines,
        public float $total,
        public float $convertedTotal,
        public string $currency,
        public string $convertedCurrency,
        public float $exchangeRate,
        public Carbon $pricedAt,
    ) {}
}
