<?php

namespace App\Http\Resources;

use App\DataTransferObjects\PortfolioLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @property-read PortfolioLine $resource
 */
class PortfolioLineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Values are keyed by the configured currency codes rather than hardcoded
     * ones, so changing the configured pair changes the payload accordingly.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $line = $this->resource;

        return [
            'id' => $line->holdingId,
            'cryptocurrency' => [
                'name' => $line->cryptocurrencyName,
                'symbol' => $line->symbol,
                'coingecko_id' => $line->coingeckoId,
            ],
            'platform' => [
                'name' => $line->platformName,
                'slug' => $line->platformSlug,
            ],
            'quantity' => $line->quantity,
            'unit_price' => $line->unitPrice,
            'value' => [
                Str::lower(config('portfolio.currencies.base')) => $line->value,
                Str::lower(config('portfolio.currencies.quote')) => $line->convertedValue,
            ],
        ];
    }
}
