<?php

namespace App\Models;

use App\Enums\AssetRequestStatus;
use App\Enums\AssetRequestType;
use Database\Factories\AssetRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['branch_id', 'user_id', 'asset_id', 'type', 'quantity', 'notes', 'status', 'reviewed_by', 'reviewed_at'])]
class AssetRequest extends Model
{
    /** @use HasFactory<AssetRequestFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AssetRequestType::class,
            'status' => AssetRequestStatus::class,
            'quantity' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * The branch this request originates from.
     *
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * The user who opened the request.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The asset being requested or offered.
     *
     * @return BelongsTo<Asset, $this>
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * The user who reviewed (approved or rejected) the request.
     *
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Determine whether the request is still awaiting review.
     */
    public function isPending(): bool
    {
        return $this->status === AssetRequestStatus::Pending;
    }
}
