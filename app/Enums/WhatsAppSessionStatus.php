<?php

namespace App\Enums;

enum WhatsAppSessionStatus: string
{
    case Stopped = 'STOPPED';
    case Starting = 'STARTING';
    case ScanQrCode = 'SCAN_QR_CODE';
    case Working = 'WORKING';
    case Failed = 'FAILED';

    public function label(): string
    {
        return match ($this) {
            self::Stopped => 'Arrêtée',
            self::Starting => 'Initialisation…',
            self::ScanQrCode => 'En attente de scan',
            self::Working => 'Connectée',
            self::Failed => 'Échec',
        };
    }
}
