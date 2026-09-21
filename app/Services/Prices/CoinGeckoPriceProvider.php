<?php

namespace App\Services\Prices;

use App\Contracts\PriceProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Quotes cryptocurrencies through the public CoinGecko API.
 *
 * Quotes are cached for a short TTL so that repeated page loads stay within
 * the API rate limit. The TTL is intentionally small: the portfolio must
 * reflect current prices on every request.
 */
class CoinGeckoPriceProvider implements PriceProvider
{
    /**
     * @param  list<string>  $coingeckoIds
     * @return array<string, float>
     *
     * @throws ConnectionException|RequestException When CoinGecko cannot be reached.
     */
    public function pricesFor(array $coingeckoIds): array
    {
        if ($coingeckoIds === []) {
            return [];
        }

        [$cached, $missing] = $this->readCache($coingeckoIds);

        if ($missing === []) {
            return $cached;
        }

        $fetched = $this->fetch($missing);

        $this->writeCache($fetched);

        return [...$cached, ...$fetched];
    }

    /**
     * Split the requested identifiers into cache hits and identifiers that
     * still have to be fetched.
     *
     * @param  list<string>  $coingeckoIds
     * @return array{0: array<string, float>, 1: list<string>}
     */
    private function readCache(array $coingeckoIds): array
    {
        $cached = [];
        $missing = [];

        foreach ($coingeckoIds as $coingeckoId) {
            $price = Cache::get($this->cacheKey($coingeckoId));

            if ($price === null) {
                $missing[] = $coingeckoId;

                continue;
            }

            $cached[$coingeckoId] = (float) $price;
        }

        return [$cached, $missing];
    }

    /**
     * @param  array<string, float>  $prices
     */
    private function writeCache(array $prices): void
    {
        $ttl = (int) config('portfolio.prices.cache_ttl');

        foreach ($prices as $coingeckoId => $price) {
            Cache::put($this->cacheKey($coingeckoId), $price, $ttl);
        }
    }

    /**
     * Fetch every missing quote in a single request.
     *
     * @param  list<string>  $coingeckoIds
     * @return array<string, float>
     *
     * @throws ConnectionException|RequestException
     */
    private function fetch(array $coingeckoIds): array
    {
        $currency = $this->currency();

        $response = Http::baseUrl(config('portfolio.coingecko.base_url'))
            ->connectTimeout(3)
            ->timeout((int) config('portfolio.coingecko.timeout'))
            ->withHeaders($this->headers())
            ->retry([200, 1000], 0, function (Throwable $exception): bool {
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429));
            })
            ->get('/simple/price', [
                'ids' => implode(',', $coingeckoIds),
                'vs_currencies' => $currency,
            ]);

        /** @var array<string, array<string, float|int>> $payload */
        $payload = $response->throw()->json();

        $prices = [];

        foreach ($payload as $coingeckoId => $quote) {
            if (isset($quote[$currency])) {
                $prices[$coingeckoId] = (float) $quote[$currency];
            }
        }

        return $prices;
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        $apiKey = config('portfolio.coingecko.api_key');

        return $apiKey ? ['x-cg-demo-api-key' => $apiKey] : [];
    }

    private function cacheKey(string $coingeckoId): string
    {
        return sprintf('portfolio:price:%s:%s', $this->currency(), $coingeckoId);
    }

    /**
     * CoinGecko expects lowercase currency codes.
     */
    private function currency(): string
    {
        return Str::lower(config('portfolio.currencies.base'));
    }
}
