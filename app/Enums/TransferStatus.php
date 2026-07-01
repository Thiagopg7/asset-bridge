<?php

namespace App\Enums;

enum TransferStatus: string
{
    case Pending = 'pending';
    case Authorized = 'authorized';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Authorized => 'Autorizada',
            self::Rejected => 'Rejeitada',
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
