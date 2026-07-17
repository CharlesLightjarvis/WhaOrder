<?php

namespace App\Enums;

enum ConversationStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'En cours',
            self::Completed => 'Terminée',
            self::Abandoned => 'Abandonnée',
        };
    }
}
