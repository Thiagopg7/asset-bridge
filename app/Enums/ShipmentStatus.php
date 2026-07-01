<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case Ready = 'ready';
    case InTransit = 'in_transit';
    case Received = 'received';

    public function label(): string
    {
        return match ($this) {
            self::Ready => 'Pronto para envio',
            self::InTransit => 'Em trânsito',
            self::Received => 'Recebido',
        };
    }

    /**
     * All status values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
