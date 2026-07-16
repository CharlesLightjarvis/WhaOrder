import { useMemo } from 'react';
import { router } from '@inertiajs/react';
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types/pagination';
import type { Address } from '@/types/address';
import AddressController from '@/actions/App/Http/Controllers/AddressController';
import { createColumns } from './columns';

type Props = {
    addresses: Paginated<Address>;
};

export default function AddressList({ addresses }: Props) {
    const columns = useMemo(() => createColumns(), []);

    const prevUrl = addresses.links[0]?.url;
    const nextUrl = addresses.links[addresses.links.length - 1]?.url;

    return (
        <div className="space-y-4">
            <DataTable
                columns={columns}
                data={addresses.data}
                searchFilter={{
                    columnIds: ['customer', 'city', 'line1'],
                    placeholder: 'Rechercher une adresse...',
                }}
                actionButton={{
                    label: 'Créer une adresse',
                    onClick: () =>
                        router.visit(AddressController.create.url()),
                }}
            />

            {addresses.last_page > 1 && (
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
                        Page {addresses.current_page} / {addresses.last_page}
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
