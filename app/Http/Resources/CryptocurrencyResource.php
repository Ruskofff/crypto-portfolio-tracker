<?php

namespace App\Http\Resources;

use App\Models\Cryptocurrency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Cryptocurrency $resource
 */
class CryptocurrencyResource extends JsonResource
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
            'name' => $this->resource->name,
            'symbol' => $this->resource->symbol,
            'coingecko_id' => $this->resource->coingecko_id,
        ];
    }
}
