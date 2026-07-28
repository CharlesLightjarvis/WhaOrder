<?php

namespace App\Actions\WhatsApp;

use App\Enums\WhatsAppSessionStatus;
use App\Models\Merchant;
use App\Models\WhatsAppSession;

class ResolveMerchantWorkingSession
{
    /**
     * A merchant can have zero, one, or several WhatsApp sessions; pick the
     * most recently active one that's actually connected, for outbound
     * messages that aren't triggered by an inbound webhook (which already
     * carries its own session).
     */
    public function handle(Merchant $merchant): ?WhatsAppSession
    {
        return $merchant->whatsAppSessions()
            ->where('status', WhatsAppSessionStatus::Working)
            ->latest('last_active_at')
            ->first();
    }
}
