<?php

namespace App\Models;

use App\Enums\AssetUnit;
use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'unit', 'active'])]
class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit' => AssetUnit::class,
            'active' => 'boolean',
        ];
    }

    /**
     * Stock items for this asset across all branches.
     *
     * @return HasMany<StockItem, $this>
     */
    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }
}
