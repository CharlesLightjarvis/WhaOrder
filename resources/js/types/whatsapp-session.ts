export type WhatsAppSessionStatus =
    | 'STOPPED'
    | 'STARTING'
    | 'SCAN_QR_CODE'
    | 'WORKING'
    | 'FAILED';

export type WhatsAppSession = {
    id: string;
    label: string;
    status: WhatsAppSessionStatus;
    status_label: string;
    phone_number: string | null;
    profile_name: string | null;
    profile_picture_url: string | null;
    qr_code: string | null;
    connected_at: string | null;
    last_active_at: string | null;
    created_at: string;
};
