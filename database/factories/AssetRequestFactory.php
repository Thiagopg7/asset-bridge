<?php

namespace Database\Factories;

use App\Enums\AssetRequestStatus;
use App\Enums\AssetRequestType;
use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetRequest>
 */
class AssetRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'asset_id' => Asset::factory(),
            'type' => fake()->randomElement(AssetRequestType::cases())->value,
            'quantity' => fake()->numberBetween(1, 50),
            'notes' => fake()->optional()->sentence(),
            'status' => AssetRequestStatus::Pending->value,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }

    /**
     * Indicate that the request is a need.
     */
    public function need(): static
    {
        return $this->state(fn (array $attributes) => ['type' => AssetRequestType::Need->value]);
    }

    /**
     * Indicate that the request is a surplus offer.
     */
    public function surplus(): static
    {
        return $this->state(fn (array $attributes) => ['type' => AssetRequestType::Surplus->value]);
    }

    /**
     * Indicate that the request has been approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AssetRequestStatus::Approved->value,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that the request has been rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AssetRequestStatus::Rejected->value,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }
}
