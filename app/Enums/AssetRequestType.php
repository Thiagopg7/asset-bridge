<?php

namespace App\Enums;

enum AssetRequestType: string
{
    case Need = 'need';
    case Surplus = 'surplus';

    public function label(): string
    {
        return match ($this) {
            self::Need => 'Necessidade',
            self::Surplus => 'Excesso',
        };
    }

    /**
     * All type values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
