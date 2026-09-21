<?php

namespace App\Services;

use App\Contracts\ExchangeRateProvider;
use App\Contracts\PriceProvider;
use App\DataTransferObjects\PortfolioLine;
use App\DataTransferObjects\PortfolioSummary;
use App\Models\Holding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Values the whole portfolio at the prices of the current request.
 *
 * Nothing is persisted: prices and the exchange rate are resolved on every
 * call, which is what keeps the web page and the API in sync with the market.
 */
class PortfolioService
{
    public function __construct(
        private readonly PriceProvider $prices,
        private readonly ExchangeRateProvider $rates,
    ) {}

    public function summary(): PortfolioSummary
    {
        $holdings = Holding::query()
            ->with(['cryptocurrency', 'platform'])
            ->get();

        $currency = config('portfolio.currencies.base');
        $convertedCurrency = config('portfolio.currencies.quote');
        $exchangeRate = $this->rates->rate($currency, $convertedCurrency);

        $prices = $this->pricesFor($holdings);

        $lines = $holdings
            ->map(fn (Holding $holding): PortfolioLine => $this->toLine($holding, $prices, $exchangeRate))
            ->sort($this->byDecreasingValue(...))
            ->values();

        return new PortfolioSummary(
            lines: $lines->all(),
            total: round($lines->sum(fn (PortfolioLine $line): float => $line->value), 2),
            convertedTotal: round($lines->sum(fn (PortfolioLine $line): float => $line->convertedValue), 2),
            currency: $currency,
            convertedCurrency: $convertedCurrency,
            exchangeRate: $exchangeRate,
            pricedAt: now(),
        );
    }

    /**
     * Quote every distinct cryptocurrency in a single provider call, so the
     * number of rows never drives the number of requests.
     *
     * @param  Collection<int, Holding>  $holdings
     * @return array<string, float>
     */
    private function pricesFor(Collection $holdings): array
    {
        $coingeckoIds = $holdings
            ->pluck('cryptocurrency.coingecko_id')
            ->unique()
            ->values()
            ->all();

        $prices = $this->prices->pricesFor($coingeckoIds);

        $unquoted = array_diff($coingeckoIds, array_keys($prices));

        if ($unquoted !== []) {
            Log::warning('Some cryptocurrencies could not be quoted and count as zero.', [
                'coingecko_ids' => array_values($unquoted),
            ]);
        }

        return $prices;
    }

    /**
     * @param  array<string, float>  $prices
     */
    private function toLine(Holding $holding, array $prices, float $exchangeRate): PortfolioLine
    {
        $quantity = (float) $holding->quantity;
        $unitPrice = $prices[$holding->cryptocurrency->coingecko_id] ?? 0.0;
        $value = round($quantity * $unitPrice, 2);

        return new PortfolioLine(
            holdingId: $holding->id,
            coingeckoId: $holding->cryptocurrency->coingecko_id,
            cryptocurrencyName: $holding->cryptocurrency->name,
            symbol: $holding->cryptocurrency->symbol,
            platformName: $holding->platform->name,
            platformSlug: $holding->platform->slug,
            quantity: $quantity,
            unitPrice: $unitPrice,
            value: $value,
            convertedValue: round($value * $exchangeRate, 2),
        );
    }

    /**
     * Sort by decreasing value, breaking ties on the holding id so that two
     * rows worth the same amount keep a stable order between requests.
     */
    private function byDecreasingValue(PortfolioLine $a, PortfolioLine $b): int
    {
        return [$b->value, $a->holdingId] <=> [$a->value, $b->holdingId];
    }
}
