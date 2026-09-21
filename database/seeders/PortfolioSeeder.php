<?php

namespace Database\Seeders;

use App\Models\Cryptocurrency;
use App\Models\Holding;
use App\Models\Platform;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class PortfolioSeeder extends Seeder
{
    /**
     * The cryptocurrencies tracked by the demo portfolio. The CoinGecko
     * identifiers must match the ones the price provider queries.
     *
     * @var list<array{coingecko_id: string, symbol: string, name: string}>
     */
    private const CRYPTOCURRENCIES = [
        ['coingecko_id' => 'bitcoin', 'symbol' => 'BTC', 'name' => 'Bitcoin'],
        ['coingecko_id' => 'ethereum', 'symbol' => 'ETH', 'name' => 'Ethereum'],
        ['coingecko_id' => 'solana', 'symbol' => 'SOL', 'name' => 'Solana'],
        ['coingecko_id' => 'cardano', 'symbol' => 'ADA', 'name' => 'Cardano'],
        ['coingecko_id' => 'ripple', 'symbol' => 'XRP', 'name' => 'XRP'],
        ['coingecko_id' => 'dogecoin', 'symbol' => 'DOGE', 'name' => 'Dogecoin'],
    ];

    /**
     * @var list<array{name: string, slug: string}>
     */
    private const PLATFORMS = [
        ['name' => 'Binance', 'slug' => 'binance'],
        ['name' => 'Kraken', 'slug' => 'kraken'],
        ['name' => 'Coinbase', 'slug' => 'coinbase'],
        ['name' => 'Ledger', 'slug' => 'ledger'],
    ];

    /**
     * Bitcoin and Ethereum are deliberately spread across several platforms to
     * exercise the one-crypto-many-platforms constraint.
     *
     * @var list<array{crypto: string, platform: string, quantity: string}>
     */
    private const HOLDINGS = [
        ['crypto' => 'bitcoin', 'platform' => 'binance', 'quantity' => '0.10000000'],
        ['crypto' => 'bitcoin', 'platform' => 'kraken', 'quantity' => '0.02000000'],
        ['crypto' => 'bitcoin', 'platform' => 'ledger', 'quantity' => '0.34500000'],
        ['crypto' => 'ethereum', 'platform' => 'binance', 'quantity' => '1.50000000'],
        ['crypto' => 'ethereum', 'platform' => 'coinbase', 'quantity' => '0.82500000'],
        ['crypto' => 'solana', 'platform' => 'kraken', 'quantity' => '12.40000000'],
        ['crypto' => 'cardano', 'platform' => 'binance', 'quantity' => '2500.00000000'],
        ['crypto' => 'ripple', 'platform' => 'coinbase', 'quantity' => '1200.00000000'],
        ['crypto' => 'dogecoin', 'platform' => 'binance', 'quantity' => '15000.00000000'],
    ];

    /**
     * Seed the demo portfolio. Running it twice leaves the same rows in place.
     */
    public function run(): void
    {
        $cryptocurrencies = $this->seedCryptocurrencies();
        $platforms = $this->seedPlatforms();

        foreach (self::HOLDINGS as $holding) {
            Holding::updateOrCreate(
                [
                    'cryptocurrency_id' => $cryptocurrencies[$holding['crypto']]->id,
                    'platform_id' => $platforms[$holding['platform']]->id,
                ],
                ['quantity' => $holding['quantity']],
            );
        }
    }

    /**
     * @return Collection<string, Cryptocurrency> Keyed by CoinGecko identifier.
     */
    private function seedCryptocurrencies(): Collection
    {
        return collect(self::CRYPTOCURRENCIES)->mapWithKeys(
            fn (array $attributes): array => [
                $attributes['coingecko_id'] => Cryptocurrency::updateOrCreate(
                    ['coingecko_id' => $attributes['coingecko_id']],
                    $attributes,
                ),
            ],
        );
    }

    /**
     * @return Collection<string, Platform> Keyed by slug.
     */
    private function seedPlatforms(): Collection
    {
        return collect(self::PLATFORMS)->mapWithKeys(
            fn (array $attributes): array => [
                $attributes['slug'] => Platform::updateOrCreate(
                    ['slug' => $attributes['slug']],
                    $attributes,
                ),
            ],
        );
    }
}
