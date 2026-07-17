export type AddressCustomer = {
    id: string;
    name: string | null;
    whatsapp_number: string;
};

export type Address = {
    id: string;
    label: string | null;
    full_name: string | null;
    phone: string | null;
    line1: string | null;
    line2: string | null;
    city: string | null;
    country: string | null;
    is_default: boolean;
    customer?: AddressCustomer;
};
