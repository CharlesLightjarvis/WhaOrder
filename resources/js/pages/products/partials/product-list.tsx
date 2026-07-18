import { useMemo } from 'react';
import { router } from '@inertiajs/react';
import { DataTable } from '@/components/data-table';
import type { Product } from '@/types/product';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { createColumns } from './columns';

const PRODUCT_STATUSES = [
    { label: 'Actif', value: 'true' },
    { label: 'Inactif', value: 'false' },
] as const;

type Props = {
    products: Product[];
};

export default function ProductList({ products }: Props) {
    const columns = useMemo(() => createColumns(), []);

    return (
        <div className="space-y-4">
            <DataTable
                columns={columns}
                data={products}
                searchFilter={{
                    columnIds: ['name'],
                    placeholder: 'Rechercher un produit...',
                }}
                facetedFilters={[
                    {
                        columnId: 'is_active',
                        title: 'Statut',
                        options: PRODUCT_STATUSES,
                    },
                ]}
                actionButton={{
                    label: 'Créer un produit',
                    onClick: () => router.visit(ProductController.create.url()),
                }}
            />
        </div>
    );
}
