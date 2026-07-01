<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Database\Factories\ShipmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['transfer_id', 'origin_branch_id', 'destination_branch_id', 'status', 'dispatched_by', 'dispatched_at', 'received_by', 'received_at'])]
class Shipment extends Model
{
    /** @use HasFactory<ShipmentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ShipmentStatus::class,
            'dispatched_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    /**
     * The authorized transfer this shipment fulfils.
     *
     * @return BelongsTo<Transfer, $this>
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    /**
     * The branch shipping the asset (the offering branch).
     *
     * @return BelongsTo<Branch, $this>
     */
    public function originBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'origin_branch_id');
    }

    /**
     * The branch receiving the asset (the requesting branch).
     *
     * @return BelongsTo<Branch, $this>
     */
    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    /**
     * The logistics user who dispatched the shipment.
     *
     * @return BelongsTo<User, $this>
     */
    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    /**
     * The user who confirmed receipt of the shipment.
     *
     * @return BelongsTo<User, $this>
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Determine whether the shipment is waiting to be dispatched.
     */
    public function isReady(): bool
    {
        return $this->status === ShipmentStatus::Ready;
    }

    /**
     * Determine whether the shipment is on its way.
     */
    public function isInTransit(): bool
    {
        return $this->status === ShipmentStatus::InTransit;
    }
}
