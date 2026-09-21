<?php

namespace Tests\Feature\Api;

use App\Contracts\PriceProvider;
use App\Models\Cryptocurrency;
use App\Models\Holding;
use App\Models\Platform;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PortfolioEndpointTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_the_portfolio_sorted_by_decreasing_value(): void
    {
        $this->holding('bitcoin', 'Binance', 0.1);   // 100
        $this->holding('dogecoin', 'Kraken', 1_000); // 10
        $this->holding('ethereum', 'Ledger', 5);     // 50

        $this->fakePrices(['bitcoin' => 1000.0, 'dogecoin' => 0.01, 'ethereum' => 10.0]);

        $response = $this->getJson(route('api.portfolio'));

        $response->assertOk();
        $this->assertSame(
            ['bitcoin', 'ethereum', 'dogecoin'],
            array_column(array_column($response->json('data.holdings'), 'cryptocurrency'), 'coingecko_id'),
        );
    }

    public function test_exposes_the_fields_required_for_every_row(): void
    {
        $this->holding('bitcoin', 'Binance', 0.5);
        $this->fakePrices(['bitcoin' => 1000.0]);
        config(['portfolio.exchange_rate.fixed' => 0.9]);

        $response = $this->getJson(route('api.portfolio'));

        $response->assertOk()->assertJsonStructure([
            'data' => [
                'holdings' => [
                    ['id', 'cryptocurrency' => ['name', 'symbol', 'coingecko_id'], 'platform' => ['name', 'slug'], 'quantity', 'unit_price', 'value' => ['usd', 'eur']],
                ],
                'total' => ['usd', 'eur'],
            ],
            'meta' => ['base_currency', 'quote_currency', 'exchange_rate', 'holdings_count', 'priced_at'],
        ]);

        // JSON has a single number type: a whole amount such as 500.0 travels
        // as 500 and decodes to an int, so the wire value is asserted as such.
        $response->assertJsonPath('data.holdings.0.quantity', 0.5)
            ->assertJsonPath('data.holdings.0.value.usd', 500)
            ->assertJsonPath('data.holdings.0.value.eur', 450)
            ->assertJsonPath('data.holdings.0.platform.name', 'Binance');
    }

    public function test_totals_match_the_sum_of_the_returned_rows(): void
    {
        $this->holding('bitcoin', 'Binance', 0.1);
        $this->holding('ethereum', 'Kraken', 2);

        $this->fakePrices(['bitcoin' => 1000.0, 'ethereum' => 10.0]);
        config(['portfolio.exchange_rate.fixed' => 0.5]);

        $response = $this->getJson(route('api.portfolio'));

        $rows = $response->json('data.holdings');

        $this->assertSame(
            array_sum(array_column(array_column($rows, 'value'), 'usd')),
            $response->json('data.total.usd'),
        );
        $this->assertSame(
            array_sum(array_column(array_column($rows, 'value'), 'eur')),
            $response->json('data.total.eur'),
        );
    }

    public function test_keeps_the_same_cryptocurrency_on_two_platforms_as_two_rows(): void
    {
        $this->holding('bitcoin', 'Binance', 0.1);
        $this->holding('bitcoin', 'Kraken', 0.02);

        $this->fakePrices(['bitcoin' => 1000.0]);

        $response = $this->getJson(route('api.portfolio'));

        $response->assertOk()
            ->assertJsonCount(2, 'data.holdings')
            ->assertJsonPath('meta.holdings_count', 2)
            ->assertJsonPath('data.holdings.0.platform.name', 'Binance')
            ->assertJsonPath('data.holdings.1.platform.name', 'Kraken');
    }

    public function test_returns_an_empty_portfolio_with_zero_totals(): void
    {
        $this->fakePrices([]);

        $response = $this->getJson(route('api.portfolio'));

        $response->assertOk()
            ->assertJsonCount(0, 'data.holdings')
            ->assertJsonPath('data.total.usd', 0)
            ->assertJsonPath('data.total.eur', 0);
    }

    public function test_recomputes_prices_on_every_call(): void
    {
        $this->holding('bitcoin', 'Binance', 1);

        $this->mock(PriceProvider::class)
            ->shouldReceive('pricesFor')
            ->twice()
            ->andReturn(['bitcoin' => 10.0], ['bitcoin' => 20.0]);

        $this->getJson(route('api.portfolio'))->assertJsonPath('data.total.usd', 10);
        $this->getJson(route('api.portfolio'))->assertJsonPath('data.total.usd', 20);
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

    private function holding(string $coingeckoId, string $platformName, float $quantity): Holding
    {
        $cryptocurrency = Cryptocurrency::firstOrCreate(
            ['coingecko_id' => $coingeckoId],
            ['symbol' => strtoupper(substr($coingeckoId, 0, 4)), 'name' => ucfirst($coingeckoId)],
        );

        $platform = Platform::firstOrCreate(
            ['slug' => strtolower($platformName)],
            ['name' => $platformName],
        );

        return Holding::create([
            'cryptocurrency_id' => $cryptocurrency->id,
            'platform_id' => $platform->id,
            'quantity' => $quantity,
        ]);
    }
}
