export type OrderStatus =
    | 'pending'
    | 'preparing'
    | 'out_for_delivery'
    | 'delivered'
    | 'cancelled';

export type PaymentStatus = 'unpaid' | 'claimed' | 'confirmed' | 'failed';

export type PaymentMethod = 'mobile_money' | 'cash' | 'card' | 'other';

export type PaymentProofType = 'screenshot' | 'message';

export type PaymentProofStatus = 'pending_review' | 'confirmed' | 'rejected';

export type DeliveryStatus = 'pending' | 'out_for_delivery' | 'delivered' | 'failed';

export type OrderItem = {
    id: string;
    product_name_snapshot: string;
    variant_name_snapshot: string | null;
    quantity: number;
    unit_price: number;
    line_total: number;
};

export type PaymentProof = {
    id: string;
    type: PaymentProofType;
    type_label: string;
    image_url: string | null;
    raw_message: string | null;
    status: PaymentProofStatus;
    status_label: string;
    reviewed_at: string | null;
};

export type Delivery = {
    id: string;
    status: DeliveryStatus;
    status_label: string;
    address_text: string | null;
    city: string | null;
    scheduled_at: string | null;
    delivered_at: string | null;
};

export type Order = {
    id: string;
    status: OrderStatus;
    status_label: string;
    payment_status: PaymentStatus;
    payment_status_label: string;
    payment_method: PaymentMethod | null;
    payment_method_label: string | null;
    delivery_address_text: string | null;
    delivery_city: string | null;
    subtotal: number;
    delivery_fee: number;
    total: number;
    customer?: {
        id: string;
        name: string | null;
        whatsapp_number: string;
    };
    items_count?: number;
    items?: OrderItem[];
    payment_proofs?: PaymentProof[];
    delivery?: Delivery | null;
    created_at: string;
};

export const ORDER_STATUSES: { value: OrderStatus; label: string }[] = [
    { value: 'pending', label: 'En attente' },
    { value: 'preparing', label: 'En préparation' },
    { value: 'out_for_delivery', label: 'En livraison' },
    { value: 'delivered', label: 'Livrée' },
    { value: 'cancelled', label: 'Annulée' },
];
