import { router } from '@inertiajs/react';
import { useMemo } from 'react';
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import type { Order } from '@/types/order';
import type { Paginated } from '@/types/pagination';
import { createColumns } from './columns';

type Props = {
    orders: Paginated<Order>;
};

export default function OrderList({ orders }: Props) {
    const columns = useMemo(() => createColumns(), []);

    const prevUrl = orders.links[0]?.url;
    const nextUrl = orders.links[orders.links.length - 1]?.url;

    return (
        <div className="space-y-4">
            <DataTable columns={columns} data={orders.data} />

            {orders.last_page > 1 && (
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
                        Page {orders.current_page} / {orders.last_page}
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
