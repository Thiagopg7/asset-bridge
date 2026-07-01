<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Diretor = 'diretor';
    case Gerente = 'gerente';
    case Colaborador = 'colaborador';
    case Logistica = 'logistica';

    /**
     * All role values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
