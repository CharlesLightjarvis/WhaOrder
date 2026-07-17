<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Claimed = 'claimed';
    case Confirmed = 'confirmed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Non payée',
            self::Claimed => 'Paiement déclaré',
            self::Confirmed => 'Paiement confirmé',
            self::Failed => 'Paiement échoué',
        };
    }
}
