export type Merchant = {
    name: string;
    whatsapp_number: string | null;
    whatsapp_admin_number: string | null;
    currency: string;
    timezone: string;
    delivery_fee: number;
};

export const TIMEZONES: { value: string; label: string }[] = [
    { value: 'Africa/Abidjan', label: 'Abidjan (GMT)' },
    { value: 'Africa/Dakar', label: 'Dakar (GMT)' },
    { value: 'Africa/Douala', label: 'Douala (GMT+1)' },
    { value: 'Africa/Lagos', label: 'Lagos (GMT+1)' },
    { value: 'Africa/Accra', label: 'Accra (GMT)' },
    { value: 'Africa/Casablanca', label: 'Casablanca (GMT+1)' },
    { value: 'Africa/Algiers', label: 'Alger (GMT+1)' },
    { value: 'Africa/Tunis', label: 'Tunis (GMT+1)' },
    { value: 'Europe/Paris', label: 'Paris (GMT+1)' },
    { value: 'Europe/London', label: 'Londres (GMT)' },
    { value: 'America/New_York', label: 'New York (GMT-5)' },
    { value: 'UTC', label: 'UTC' },
];
