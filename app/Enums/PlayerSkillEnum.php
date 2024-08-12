<?php

namespace App\Enums;

enum PlayerSkillEnum: string
{
    case DEFENSE = 'defense';
    case ATTACK = 'attack';
    case SPEED = 'speed';
    case STRENGTH = 'strength';
    case STAMINA = 'stamina';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function descripcion(): string
    {
        return match ($this)
        {
            self::DEFENSE => 'defense',
            self::ATTACK => 'attack',
            self::SPEED => 'speed',
            self::STRENGTH => 'strength',
            self::STAMINA => 'stamina'
        };
    }
}