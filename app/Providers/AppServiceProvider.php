<?php

namespace App\Providers;

use App\Contracts\PriceProvider;
use App\Services\Prices\CoinGeckoPriceProvider;
use App\Services\Prices\FakePriceProvider;
use App\Services\Prices\FallbackPriceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerPriceProvider();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Resolve the price provider from `config/portfolio.php`, optionally
     * wrapping the remote one so a failing API degrades to simulated prices.
     */
    private function registerPriceProvider(): void
    {
        $this->app->singleton(PriceProvider::class, function (Application $app): PriceProvider {
            if (config('portfolio.prices.driver') === 'fake') {
                return $app->make(FakePriceProvider::class);
            }

            $provider = $app->make(CoinGeckoPriceProvider::class);

            if (! config('portfolio.prices.fallback')) {
                return $provider;
            }

            return new FallbackPriceProvider($provider, $app->make(FakePriceProvider::class));
        });
    }
}
