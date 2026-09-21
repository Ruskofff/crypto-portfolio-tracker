<?php

namespace App\Models;

use Database\Factories\CryptocurrencyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['coingecko_id', 'symbol', 'name'])]
class Cryptocurrency extends Model
{
    /** @use HasFactory<CryptocurrencyFactory> */
    use HasFactory;

    /**
     * The holdings of this cryptocurrency, across every platform.
     *
     * @return HasMany<Holding, $this>
     */
    public function holdings(): HasMany
    {
        return $this->hasMany(Holding::class);
    }
}
