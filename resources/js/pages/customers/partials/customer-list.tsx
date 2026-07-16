import { useMemo } from 'react';
import { router } from '@inertiajs/react';
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types/pagination';
import type { Customer } from '@/types/customer';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import { createColumns } from './columns';

type Props = {
    customers: Paginated<Customer>;
};

export default function CustomerList({ customers }: Props) {
    const columns = useMemo(() => createColumns(), []);

    const prevUrl = customers.links[0]?.url;
    const nextUrl = customers.links[customers.links.length - 1]?.url;

    return (
        <div className="space-y-4">
            <DataTable
                columns={columns}
                data={customers.data}
                searchFilter={{
                    columnIds: ['name', 'whatsapp_number'],
                    placeholder: 'Rechercher un client...',
                }}
                actionButton={{
                    label: 'Créer un client',
                    onClick: () =>
                        router.visit(CustomerController.create.url()),
                }}
            />

            {customers.last_page > 1 && (
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
                        Page {customers.current_page} / {customers.last_page}
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
