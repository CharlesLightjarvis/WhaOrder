<?php

namespace App\Enums;

enum MessageIntent: string
{
    case Order = 'order';
    case Support = 'support';
    case Social = 'social';
}
