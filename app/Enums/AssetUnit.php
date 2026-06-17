<?php

namespace App\Enums;

enum AssetUnit: string
{
    case Un = 'un';
    case Cx = 'cx';
    case Kg = 'kg';
    case Lt = 'lt';
    case M = 'm';

    public function label(): string
    {
        return match ($this) {
            self::Un => 'Unidade',
            self::Cx => 'Caixa',
            self::Kg => 'Quilograma',
            self::Lt => 'Litro',
            self::M => 'Metro',
        };
    }

    /**
     * All unit values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
