import type { Category } from './category';

export type ProductImage = {
    id: string;
    url: string;
};

export type ProductVariant = {
    id: string;
    name: string;
    price: number | null;
    stock: number;
    images?: ProductImage[];
};

export type Product = {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    price: number | null;
    stock: number | null;
    has_variants: boolean;
    cover_image: ProductImage | null;
    price_min: number | null;
    price_max: number | null;
    stock_total: number | null;
    is_active: boolean;
    category?: Category | null;
    images?: ProductImage[];
    variants?: ProductVariant[];
    variants_count?: number;
    created_at: string;
    updated_at: string;
};
