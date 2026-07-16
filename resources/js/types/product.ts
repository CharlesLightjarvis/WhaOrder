import type { Category } from './category';

export type ProductImage = {
    id: number;
    url: string;
};

export type ProductVariant = {
    id: number;
    name: string;
    price: number | null;
    stock: number;
    images?: ProductImage[];
};

export type Product = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price: number;
    stock: number;
    is_active: boolean;
    category?: Category | null;
    images?: ProductImage[];
    variants?: ProductVariant[];
    variants_count?: number;
    created_at: string;
    updated_at: string;
};
