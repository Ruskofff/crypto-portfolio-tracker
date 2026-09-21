<?php

namespace Database\Factories;

use App\Models\Cryptocurrency;
use App\Models\Holding;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holding>
 */
class HoldingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cryptocurrency_id' => Cryptocurrency::factory(),
            'platform_id' => Platform::factory(),
            'quantity' => fake()->randomFloat(8, 0.0001, 25),
        ];
    }

    /**
     * Indicate a holding large enough to dominate the portfolio total.
     */
    public function whale(): static
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => fake()->randomFloat(8, 1_000, 100_000),
        ]);
    }
}
