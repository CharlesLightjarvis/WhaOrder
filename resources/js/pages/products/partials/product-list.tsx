import { useMemo } from 'react';
import { router } from '@inertiajs/react';
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types/pagination';
import type { Product } from '@/types/product';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { createColumns } from './columns';

const PRODUCT_STATUSES = [
    { label: 'Actif', value: 'true' },
    { label: 'Inactif', value: 'false' },
] as const;

type Props = {
    products: Paginated<Product>;
};

export default function ProductList({ products }: Props) {
    const columns = useMemo(() => createColumns(), []);

    const prevUrl = products.links[0]?.url;
    const nextUrl = products.links[products.links.length - 1]?.url;

    return (
        <div className="space-y-4">
            <DataTable
                columns={columns}
                data={products.data}
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

            {products.last_page > 1 && (
                <div className="flex items-center justify-end gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        disabled={!prevUrl}
                        onClick={() => prevUrl && router.visit(prevUrl)}
                    >
                        Précédent
                    </Button>
                    <span className="text-sm text-muted-foreground">
                        Page {products.current_page} / {products.last_page}
                    </span>
                    <Button
                        variant="outline"
                        size="sm"
                        disabled={!nextUrl}
                        onClick={() => nextUrl && router.visit(nextUrl)}
                    >
                        Suivant
                    </Button>
                </div>
            )}
        </div>
    );
}
