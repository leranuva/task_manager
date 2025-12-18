<?php

namespace App\Enums;

enum TeamRole: string
{
    case ADMIN = 'admin';
    case MEMBER = 'member';
    case VIEWER = 'viewer';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrador',
            self::MEMBER => 'Miembro',
            self::VIEWER => 'Visualizador',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

