import type { Address } from './address';

export type Customer = {
    id: string;
    whatsapp_number: string;
    name: string | null;
    notes: string | null;
    last_order_at: string | null;
    addresses_count?: number;
    addresses?: Address[];
    created_at: string;
    updated_at: string;
};
