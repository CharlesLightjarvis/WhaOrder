<?php

namespace App\Enums;

enum OrderModificationAction: string
{
    case AddItem = 'add_item';
    case RemoveItem = 'remove_item';
    case ChangeQuantity = 'change_quantity';
    case ChangeDelivery = 'change_delivery';
    case ChangePaymentMethod = 'change_payment_method';
}
