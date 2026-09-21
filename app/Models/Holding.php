<?php

namespace App\Models;

use Database\Factories\HoldingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A quantity of one cryptocurrency held on one platform.
 */
#[Fillable(['cryptocurrency_id', 'platform_id', 'quantity'])]
class Holding extends Model
{
    /** @use HasFactory<HoldingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:8',
        ];
    }

    /**
     * @return BelongsTo<Cryptocurrency, $this>
     */
    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class);
    }

    /**
     * @return BelongsTo<Platform, $this>
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
