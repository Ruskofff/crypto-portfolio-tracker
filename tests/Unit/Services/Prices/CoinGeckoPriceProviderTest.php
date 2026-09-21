<?php

namespace Tests\Unit\Services\Prices;

use App\Services\Prices\CoinGeckoPriceProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoinGeckoPriceProviderTest extends TestCase
{
    private const ENDPOINT = 'api.coingecko.com/api/v3/simple/price*';

    public function test_returns_the_price_of_each_requested_cryptocurrency(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            self::ENDPOINT => Http::response([
                'bitcoin' => ['usd' => 64000.5],
                'solana' => ['usd' => 145.25],
            ]),
        ]);

        $prices = (new CoinGeckoPriceProvider)->pricesFor(['bitcoin', 'solana']);

        $this->assertSame(['bitcoin' => 64000.5, 'solana' => 145.25], $prices);
    }

    public function test_requests_every_identifier_in_a_single_call(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            self::ENDPOINT => Http::response([
                'bitcoin' => ['usd' => 1.0],
                'ethereum' => ['usd' => 2.0],
                'solana' => ['usd' => 3.0],
            ]),
        ]);

        (new CoinGeckoPriceProvider)->pricesFor(['bitcoin', 'ethereum', 'solana']);

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request): bool => $request['ids'] === 'bitcoin,ethereum,solana'
            && $request['vs_currencies'] === 'usd');
    }

    public function test_serves_a_cached_quote_without_calling_the_api(): void
    {
        Cache::put('portfolio:price:usd:bitcoin', 42.0, 60);

        Http::preventStrayRequests();
        Http::fake([self::ENDPOINT => Http::response([])]);

        $prices = (new CoinGeckoPriceProvider)->pricesFor(['bitcoin']);

        $this->assertSame(['bitcoin' => 42.0], $prices);
        Http::assertNothingSent();
    }

    public function test_only_requests_the_identifiers_missing_from_the_cache(): void
    {
        Cache::put('portfolio:price:usd:bitcoin', 42.0, 60);

        Http::preventStrayRequests();
        Http::fake([
            self::ENDPOINT => Http::response(['solana' => ['usd' => 10.0]]),
        ]);

        $prices = (new CoinGeckoPriceProvider)->pricesFor(['bitcoin', 'solana']);

        $this->assertSame(['bitcoin' => 42.0, 'solana' => 10.0], $prices);
        Http::assertSent(fn (Request $request): bool => $request['ids'] === 'solana');
    }

    public function test_caches_a_freshly_fetched_quote(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            self::ENDPOINT => Http::response(['bitcoin' => ['usd' => 99.0]]),
        ]);

        (new CoinGeckoPriceProvider)->pricesFor(['bitcoin']);

        $this->assertSame(99.0, Cache::get('portfolio:price:usd:bitcoin'));
    }

    public function test_omits_a_cryptocurrency_the_api_does_not_quote(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            self::ENDPOINT => Http::response(['bitcoin' => ['usd' => 64000.0]]),
        ]);

        $prices = (new CoinGeckoPriceProvider)->pricesFor(['bitcoin', 'unknown-coin']);

        $this->assertSame(['bitcoin' => 64000.0], $prices);
    }

    public function test_throws_when_the_api_returns_an_error(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::ENDPOINT => Http::response(status: 500)]);

        $this->expectException(RequestException::class);

        (new CoinGeckoPriceProvider)->pricesFor(['bitcoin']);
    }

    public function test_does_not_call_the_api_for_an_empty_list(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::ENDPOINT => Http::response([])]);

        $this->assertSame([], (new CoinGeckoPriceProvider)->pricesFor([]));

        Http::assertNothingSent();
    }
}
