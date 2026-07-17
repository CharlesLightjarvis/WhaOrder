<?php

namespace App\Enums;

enum PaymentProofStatus: string
{
    case PendingReview = 'pending_review';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PendingReview => 'À vérifier',
            self::Confirmed => 'Confirmée',
            self::Rejected => 'Rejetée',
        };
    }
}
