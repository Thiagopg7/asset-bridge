<?php

namespace Database\Factories;

use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transfer = Transfer::factory()->authorized()->create();

        return [
            'transfer_id' => $transfer->id,
            'origin_branch_id' => $transfer->assetRequest->branch_id,
            'destination_branch_id' => $transfer->branch_id,
            'status' => ShipmentStatus::Ready->value,
            'dispatched_by' => null,
            'dispatched_at' => null,
            'received_by' => null,
            'received_at' => null,
        ];
    }

    /**
     * Build the shipment from an existing transfer, keeping the branches consistent.
     */
    public function forTransfer(Transfer $transfer): static
    {
        return $this->state(fn (array $attributes) => [
            'transfer_id' => $transfer->id,
            'origin_branch_id' => $transfer->assetRequest->branch_id,
            'destination_branch_id' => $transfer->branch_id,
        ]);
    }

    /**
     * Indicate that the shipment is on its way.
     */
    public function inTransit(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShipmentStatus::InTransit->value,
            'dispatched_by' => User::factory(),
            'dispatched_at' => now(),
        ]);
    }

    /**
     * Indicate that the shipment has been received.
     */
    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShipmentStatus::Received->value,
            'dispatched_by' => User::factory(),
            'dispatched_at' => now(),
            'received_by' => User::factory(),
            'received_at' => now(),
        ]);
    }
}
