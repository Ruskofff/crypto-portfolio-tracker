<?php

namespace App\Http\Resources;

use App\DataTransferObjects\PortfolioSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @property-read PortfolioSummary $resource
 */
class PortfolioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $summary = $this->resource;

        return [
            'holdings' => PortfolioLineResource::collection($summary->lines),
            'total' => [
                Str::lower($summary->currency) => $summary->total,
                Str::lower($summary->convertedCurrency) => $summary->convertedTotal,
            ],
        ];
    }

    /**
     * Describe how the payload was valued, so a client can tell which rate and
     * which instant the figures come from.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        $summary = $this->resource;

        return [
            'meta' => [
                'base_currency' => $summary->currency,
                'quote_currency' => $summary->convertedCurrency,
                'exchange_rate' => $summary->exchangeRate,
                'holdings_count' => count($summary->lines),
                'priced_at' => $summary->pricedAt->toIso8601String(),
            ],
        ];
    }
}
