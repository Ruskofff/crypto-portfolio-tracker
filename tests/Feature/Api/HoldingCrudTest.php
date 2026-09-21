<?php

namespace Tests\Feature\Api;

use App\Models\Cryptocurrency;
use App\Models\Holding;
use App\Models\Platform;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HoldingCrudTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_lists_every_stored_holding_with_its_relations(): void
    {
        Holding::factory()->count(3)->create();

        $response = $this->getJson(route('api.holdings.index'));

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    ['id', 'quantity', 'cryptocurrency' => ['id', 'name', 'symbol'], 'platform' => ['id', 'name', 'slug']],
                ],
            ]);
    }

    public function test_valid_payload_creates_a_holding_and_returns_201(): void
    {
        $cryptocurrency = Cryptocurrency::factory()->create();
        $platform = Platform::factory()->create();

        $response = $this->postJson(route('api.holdings.store'), [
            'cryptocurrency_id' => $cryptocurrency->id,
            'platform_id' => $platform->id,
            'quantity' => 1.23456789,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.quantity', 1.23456789)
            ->assertJsonPath('data.cryptocurrency.id', $cryptocurrency->id)
            ->assertJsonPath('data.platform.id', $platform->id);

        $this->assertDatabaseHas('holdings', [
            'cryptocurrency_id' => $cryptocurrency->id,
            'platform_id' => $platform->id,
        ]);
    }

    public function test_returns_422_when_the_cryptocurrency_already_has_a_holding_on_that_platform(): void
    {
        $existing = Holding::factory()->create();

        $response = $this->postJson(route('api.holdings.store'), [
            'cryptocurrency_id' => $existing->cryptocurrency_id,
            'platform_id' => $existing->platform_id,
            'quantity' => 5,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('platform_id');
        $this->assertSame(1, Holding::count());
    }

    public function test_allows_the_same_cryptocurrency_on_a_different_platform(): void
    {
        $existing = Holding::factory()->create();
        $otherPlatform = Platform::factory()->create();

        $response = $this->postJson(route('api.holdings.store'), [
            'cryptocurrency_id' => $existing->cryptocurrency_id,
            'platform_id' => $otherPlatform->id,
            'quantity' => 5,
        ]);

        $response->assertCreated();
        $this->assertSame(2, Holding::count());
    }

    public function test_returns_422_when_the_quantity_is_not_positive(): void
    {
        $cryptocurrency = Cryptocurrency::factory()->create();
        $platform = Platform::factory()->create();

        $response = $this->postJson(route('api.holdings.store'), [
            'cryptocurrency_id' => $cryptocurrency->id,
            'platform_id' => $platform->id,
            'quantity' => 0,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->assertSame(0, Holding::count());
    }

    public function test_returns_422_when_the_cryptocurrency_does_not_exist(): void
    {
        $platform = Platform::factory()->create();

        $response = $this->postJson(route('api.holdings.store'), [
            'cryptocurrency_id' => 9999,
            'platform_id' => $platform->id,
            'quantity' => 1,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('cryptocurrency_id');
    }

    public function test_returns_422_when_required_fields_are_missing(): void
    {
        $response = $this->postJson(route('api.holdings.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cryptocurrency_id', 'platform_id', 'quantity']);
    }

    public function test_updates_a_holding_and_returns_200(): void
    {
        $holding = Holding::factory()->create(['quantity' => 1]);

        $response = $this->putJson(route('api.holdings.update', $holding), [
            'cryptocurrency_id' => $holding->cryptocurrency_id,
            'platform_id' => $holding->platform_id,
            'quantity' => 7.5,
        ]);

        $response->assertOk()->assertJsonPath('data.quantity', 7.5);
        $this->assertSame('7.50000000', $holding->fresh()->quantity);
    }

    public function test_updating_a_holding_without_changing_its_pair_does_not_trip_the_unique_rule(): void
    {
        $holding = Holding::factory()->create(['quantity' => 1]);

        $this->putJson(route('api.holdings.update', $holding), [
            'cryptocurrency_id' => $holding->cryptocurrency_id,
            'platform_id' => $holding->platform_id,
            'quantity' => 2,
        ])->assertOk();
    }

    public function test_returns_422_when_an_update_would_duplicate_another_pair(): void
    {
        $first = Holding::factory()->create();
        $second = Holding::factory()->create();

        $response = $this->putJson(route('api.holdings.update', $second), [
            'cryptocurrency_id' => $first->cryptocurrency_id,
            'platform_id' => $first->platform_id,
            'quantity' => 3,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('platform_id');
    }

    public function test_deletes_a_holding_and_returns_204(): void
    {
        $holding = Holding::factory()->create();

        $this->deleteJson(route('api.holdings.destroy', $holding))->assertNoContent();

        $this->assertDatabaseMissing('holdings', ['id' => $holding->id]);
    }

    public function test_returns_404_for_an_unknown_holding(): void
    {
        $this->getJson(route('api.holdings.show', 9999))->assertNotFound();
    }

    public function test_lists_the_reference_data_the_form_needs(): void
    {
        Cryptocurrency::factory()->count(2)->create();
        Platform::factory()->count(3)->create();

        $this->getJson(route('api.cryptocurrencies.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'symbol', 'coingecko_id']]]);

        $this->getJson(route('api.platforms.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'slug']]]);
    }
}
