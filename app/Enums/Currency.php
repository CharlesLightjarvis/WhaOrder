<?php

namespace App\Enums;

enum Currency: string
{
    case Xof = 'XOF';
    case Xaf = 'XAF';
    case Ngn = 'NGN';
    case Ghs = 'GHS';
    case Mad = 'MAD';
    case Eur = 'EUR';
    case Usd = 'USD';
    case Gbp = 'GBP';

    public function label(): string
    {
        return match ($this) {
            self::Xof => 'Franc CFA (BCEAO) — XOF',
            self::Xaf => 'Franc CFA (BEAC) — XAF',
            self::Ngn => 'Naira — NGN',
            self::Ghs => 'Cedi — GHS',
            self::Mad => 'Dirham — MAD',
            self::Eur => 'Euro — EUR',
            self::Usd => 'Dollar américain — USD',
            self::Gbp => 'Livre sterling — GBP',
        };
    }
}
