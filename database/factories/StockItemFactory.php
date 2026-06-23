<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Branch;
use App\Models\StockItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockItem>
 */
class StockItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'branch_id' => Branch::factory(),
            'quantity' => fake()->numberBetween(0, 100),
        ];
    }
}
