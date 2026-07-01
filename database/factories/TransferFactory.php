<?php

namespace Database\Factories;

use App\Enums\TransferStatus;
use App\Models\AssetRequest;
use App\Models\Branch;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transfer>
 */
class TransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_request_id' => AssetRequest::factory()->surplus()->approved(),
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'quantity' => fake()->numberBetween(1, 10),
            'notes' => fake()->optional()->sentence(),
            'status' => TransferStatus::Pending->value,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }

    /**
     * Indicate that the transfer has been authorized.
     */
    public function authorized(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransferStatus::Authorized->value,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that the transfer has been rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransferStatus::Rejected->value,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }
}
