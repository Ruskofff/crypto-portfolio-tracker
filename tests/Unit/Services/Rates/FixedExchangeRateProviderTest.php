<?php

namespace Tests\Unit\Services\Rates;

use App\Services\Rates\FixedExchangeRateProvider;
use InvalidArgumentException;
use Tests\TestCase;

class FixedExchangeRateProviderTest extends TestCase
{
    public function test_returns_the_configured_rate_for_the_base_pair(): void
    {
        config(['portfolio.exchange_rate.fixed' => 0.9]);

        $rate = (new FixedExchangeRateProvider)->rate('USD', 'EUR');

        $this->assertSame(0.9, $rate);
    }

    public function test_returns_the_inverse_rate_for_the_reversed_pair(): void
    {
        config(['portfolio.exchange_rate.fixed' => 0.5]);

        $rate = (new FixedExchangeRateProvider)->rate('EUR', 'USD');

        $this->assertSame(2.0, $rate);
    }

    public function test_returns_one_when_both_currencies_are_the_same(): void
    {
        $rate = (new FixedExchangeRateProvider)->rate('USD', 'USD');

        $this->assertSame(1.0, $rate);
    }

    public function test_accepts_lowercase_currency_codes(): void
    {
        config(['portfolio.exchange_rate.fixed' => 0.9]);

        $rate = (new FixedExchangeRateProvider)->rate('usd', 'eur');

        $this->assertSame(0.9, $rate);
    }

    public function test_rejects_a_pair_it_cannot_quote(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No fixed rate is configured for USD to GBP.');

        (new FixedExchangeRateProvider)->rate('USD', 'GBP');
    }
}
