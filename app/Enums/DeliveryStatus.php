<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::OutForDelivery => 'En livraison',
            self::Delivered => 'Livrée',
            self::Failed => 'Échec',
        };
    }
}
