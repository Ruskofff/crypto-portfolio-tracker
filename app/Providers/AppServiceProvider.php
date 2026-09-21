<?php

namespace App\Providers;

use App\Contracts\ExchangeRateProvider;
use App\Contracts\PriceProvider;
use App\Services\Prices\CoinGeckoPriceProvider;
use App\Services\Prices\FakePriceProvider;
use App\Services\Prices\FallbackPriceProvider;
use App\Services\Rates\FallbackExchangeRateProvider;
use App\Services\Rates\FixedExchangeRateProvider;
use App\Services\Rates\FrankfurterExchangeRateProvider;
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
        $this->registerExchangeRateProvider();
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

    /**
     * Resolve the exchange rate provider from `config/portfolio.php`, falling
     * back to the configured fixed rate when the remote API is unavailable.
     */
    private function registerExchangeRateProvider(): void
    {
        $this->app->singleton(ExchangeRateProvider::class, function (Application $app): ExchangeRateProvider {
            if (config('portfolio.exchange_rate.driver') === 'fixed') {
                return $app->make(FixedExchangeRateProvider::class);
            }

            $provider = $app->make(FrankfurterExchangeRateProvider::class);

            if (! config('portfolio.exchange_rate.fallback')) {
                return $provider;
            }

            return new FallbackExchangeRateProvider($provider, $app->make(FixedExchangeRateProvider::class));
        });
    }
}
