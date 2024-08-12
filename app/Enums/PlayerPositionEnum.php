<?php

namespace App\Enums;

enum PlayerPositionEnum: string
{
    case DEFENDER = 'defender';
    case MIDFIELDER = 'midfielder';
    case FORWARD = 'forward';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function descripcion(): string
    {
        return match ($this)
        {
            self::DEFENDER => 'defender',
            self::MIDFIELDER => 'midfielder',
            self::FORWARD => 'forward'
        };
    }
}
