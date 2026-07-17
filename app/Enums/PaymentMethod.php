<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case MobileMoney = 'mobile_money';
    case Cash = 'cash';
    case Card = 'card';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MobileMoney => 'Mobile Money',
            self::Cash => 'Espèces à la livraison',
            self::Card => 'Carte bancaire',
            self::Other => 'Autre',
        };
    }
}
