<?php

namespace App\Models;

use App\Concerns\BelongsToMerchant;
use App\Enums\WhatsAppSessionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property WhatsAppSessionStatus $status
 * @property Carbon|null $connected_at
 * @property Carbon|null $last_active_at
 */
#[Fillable(['label', 'waha_session_name', 'status', 'phone_number', 'profile_name', 'profile_picture_url', 'qr_code', 'connected_at', 'last_active_at'])]
class WhatsAppSession extends Model
{
    use BelongsToMerchant, HasUuids;

    protected $table = 'whatsapp_sessions';

    protected function casts(): array
    {
        return [
            'status' => WhatsAppSessionStatus::class,
            'connected_at' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }
}
