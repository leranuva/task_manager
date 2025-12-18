<?php

namespace App\Enums;

enum ProjectRole: string
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrador',
            self::EDITOR => 'Editor',
            self::VIEWER => 'Visualizador',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

