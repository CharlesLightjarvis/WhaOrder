<?php

namespace App\Actions\WhatsApp;

class BuildWhatsAppChatId
{
    public function handle(string $phoneNumber): string
    {
        return preg_replace('/\D/', '', $phoneNumber).'@c.us';
    }
}
