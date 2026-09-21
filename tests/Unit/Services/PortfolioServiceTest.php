<?php

namespace Tests\Unit\Services;

use App\Contracts\PriceProvider;
use App\DataTransferObjects\PortfolioLine;
use App\DataTransferObjects\PortfolioSummary;
use App\Models\Cryptocurrency;
use App\Models\Holding;
use App\Models\Platform;
use App\Services\PortfolioService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PortfolioServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_orders_lines_by_decreasing_value_rather_than_by_quantity(): void
    {
        $this->holding('bitcoin', 'ledger', 0.5);      // 0.5 * 100 = 50
        $this->holding('dogecoin', 'binance', 10_000); // 10000 * 0.002 = 20
        $this->holding('ethereum', 'kraken', 4);       // 4 * 10 = 40

        $this->fakePrices(['bitcoin' => 100.0, 'dogecoin' => 0.002, 'ethereum' => 10.0]);

        $lines = $this->summary()->lines;

        $this->assertSame(
            ['bitcoin', 'ethereum', 'dogecoin'],
            array_map(fn (PortfolioLine $line): string => $line->coingeckoId, $lines),
        );
        $this->assertSame([50.0, 40.0, 20.0], array_map(fn (PortfolioLine $line): float => $line->value, $lines));
    }

    public function test_keeps_the_same_cryptocurrency_on_several_platforms_as_separate_lines(): void
    {
        $this->holding('bitcoin', 'binance', 0.1);
        $this->holding('bitcoin', 'kraken', 0.02);

        $this->fakePrices(['bitcoin' => 1000.0]);

        $lines = $this->summary()->lines;

        $this->assertCount(2, $lines);
        $this->assertSame(['binance', 'kraken'], array_map(
            fn (PortfolioLine $line): string => $line->platformSlug,
            $lines,
        ));
        $this->assertSame([100.0, 20.0], array_map(fn (PortfolioLine $line): float => $line->value, $lines));
    }

    public function test_converts_each_line_with_the_exchange_rate(): void
    {
        $this->holding('bitcoin', 'ledger', 2);
        $this->fakePrices(['bitcoin' => 100.0]);
        config(['portfolio.exchange_rate.fixed' => 0.9]);

        $line = $this->summary()->lines[0];

        $this->assertSame(200.0, $line->value);
        $this->assertSame(180.0, $line->convertedValue);
    }

    public function test_totals_are_the_sum_of_the_displayed_line_values(): void
    {
        $this->holding('bitcoin', 'ledger', 1);
        $this->holding('ethereum', 'kraken', 3);

        $this->fakePrices(['bitcoin' => 10.005, 'ethereum' => 1.111]);
        config(['portfolio.exchange_rate.fixed' => 0.5]);

        $summary = $this->summary();

        $lineTotal = array_sum(array_map(fn (PortfolioLine $line): float => $line->value, $summary->lines));

        $this->assertSame($lineTotal, $summary->total);
        $this->assertSame(
            array_sum(array_map(fn (PortfolioLine $line): float => $line->convertedValue, $summary->lines)),
            $summary->convertedTotal,
        );
    }

    public function test_quotes_every_distinct_cryptocurrency_in_a_single_provider_call(): void
    {
        $this->holding('bitcoin', 'binance', 1);
        $this->holding('bitcoin', 'kraken', 1);
        $this->holding('ethereum', 'ledger', 1);

        $provider = $this->mock(PriceProvider::class);
        $provider->shouldReceive('pricesFor')
            ->once()
            ->withArgs(fn (array $ids): bool => $ids === ['bitcoin', 'ethereum'])
            ->andReturn(['bitcoin' => 1.0, 'ethereum' => 1.0]);

        // Three rows, two distinct cryptocurrencies, one provider call.
        $this->assertCount(3, $this->summary()->lines);
    }

    public function test_values_an_unquoted_cryptocurrency_at_zero(): void
    {
        $this->holding('bitcoin', 'ledger', 3);
        $this->fakePrices([]);

        $line = $this->summary()->lines[0];

        $this->assertSame(0.0, $line->unitPrice);
        $this->assertSame(0.0, $line->value);
    }

    public function test_returns_zero_totals_when_the_portfolio_is_empty(): void
    {
        $this->fakePrices([]);

        $summary = $this->summary();

        $this->assertSame([], $summary->lines);
        $this->assertSame(0.0, $summary->total);
        $this->assertSame(0.0, $summary->convertedTotal);
    }

    public function test_reports_the_currencies_and_rate_used_for_the_valuation(): void
    {
        $this->fakePrices([]);
        config(['portfolio.exchange_rate.fixed' => 0.87]);

        $summary = $this->summary();

        $this->assertSame('USD', $summary->currency);
        $this->assertSame('EUR', $summary->convertedCurrency);
        $this->assertSame(0.87, $summary->exchangeRate);
    }

    private function summary(): PortfolioSummary
    {
        return $this->app->make(PortfolioService::class)->summary();
    }

    /**
     * @param  array<string, float>  $prices
     */
    private function fakePrices(array $prices): void
    {
        $this->mock(PriceProvider::class)
            ->shouldReceive('pricesFor')
            ->andReturn($prices);
    }

    private function holding(string $coingeckoId, string $platformSlug, float $quantity): Holding
    {
        $cryptocurrency = Cryptocurrency::firstOrCreate(
            ['coingecko_id' => $coingeckoId],
            ['symbol' => strtoupper(substr($coingeckoId, 0, 4)), 'name' => ucfirst($coingeckoId)],
        );

        $platform = Platform::firstOrCreate(
            ['slug' => $platformSlug],
            ['name' => ucfirst($platformSlug)],
        );

        return Holding::create([
            'cryptocurrency_id' => $cryptocurrency->id,
            'platform_id' => $platform->id,
            'quantity' => $quantity,
        ]);
    }
}
