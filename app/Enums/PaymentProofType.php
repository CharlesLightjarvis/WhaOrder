<?php

namespace App\Enums;

enum PaymentProofType: string
{
    case Screenshot = 'screenshot';
    case Message = 'message';

    public function label(): string
    {
        return match ($this) {
            self::Screenshot => 'Capture d’écran',
            self::Message => 'Message texte',
        };
    }
}
