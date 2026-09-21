<?php

namespace Tests\Unit\Services\Prices;

use App\Contracts\PriceProvider;
use App\Services\Prices\FakePriceProvider;
use App\Services\Prices\FallbackPriceProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class FallbackPriceProviderTest extends TestCase
{
    public function test_returns_the_primary_prices_when_it_succeeds(): void
    {
        $primary = $this->mock(PriceProvider::class);
        $primary->shouldReceive('pricesFor')->once()->andReturn(['bitcoin' => 1234.0]);

        $fallback = $this->mock(FakePriceProvider::class);
        $fallback->shouldNotReceive('pricesFor');

        $provider = new FallbackPriceProvider($primary, $fallback);

        $this->assertSame(['bitcoin' => 1234.0], $provider->pricesFor(['bitcoin']));
    }

    public function test_falls_back_to_simulated_prices_when_the_primary_fails(): void
    {
        Log::spy();

        $primary = $this->mock(PriceProvider::class);
        $primary->shouldReceive('pricesFor')
            ->once()
            ->andThrow(new ConnectionException('Could not resolve host.'));

        $provider = new FallbackPriceProvider($primary, new FakePriceProvider);

        $prices = $provider->pricesFor(['bitcoin']);

        $this->assertSame(['bitcoin' => 64250.00], $prices);
        Log::shouldHaveReceived('warning')->once();
    }
}
