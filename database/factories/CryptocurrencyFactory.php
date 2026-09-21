<?php

namespace Database\Factories;

use App\Models\Cryptocurrency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cryptocurrency>
 */
class CryptocurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'coingecko_id' => Str::slug($name),
            'symbol' => fake()->unique()->regexify('[A-Z]{3,4}'),
            'name' => Str::title($name),
        ];
    }
}
