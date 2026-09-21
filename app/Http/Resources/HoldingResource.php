<?php

namespace App\Http\Resources;

use App\Models\Holding;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The stored side of a holding: what the user owns, without any valuation.
 * Prices belong to the portfolio endpoint, which recomputes them per request.
 *
 * @property-read Holding $resource
 */
class HoldingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'quantity' => (float) $this->resource->quantity,
            'cryptocurrency' => new CryptocurrencyResource(
                $this->whenLoaded('cryptocurrency')
            ),
            'platform' => new PlatformResource(
                $this->whenLoaded('platform')
            ),
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
        ];
    }
}
