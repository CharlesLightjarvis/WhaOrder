import { type ColumnDef } from '@tanstack/react-table';
import { Link } from '@inertiajs/react';
import { EyeIcon } from 'lucide-react';
import { DataTableColumnHeader } from '@/components/data-table-column-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import type { Order, OrderStatus, PaymentStatus } from '@/types/order';

const orderStatusVariant: Record<
    OrderStatus,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    pending: 'outline',
    preparing: 'secondary',
    out_for_delivery: 'secondary',
    delivered: 'default',
    cancelled: 'destructive',
};

const paymentStatusVariant: Record<
    PaymentStatus,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    unpaid: 'outline',
    claimed: 'secondary',
    confirmed: 'default',
    failed: 'destructive',
};

export const createColumns = (): ColumnDef<Order>[] => [
    {
        accessorKey: 'customer',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Client" />
        ),
        cell: ({ row }) => (
            <span className="font-medium">
                {row.original.customer?.name ??
                    row.original.customer?.whatsapp_number ?? (
                        <span className="text-muted-foreground italic">
                            Client supprimé
                        </span>
                    )}
            </span>
        ),
    },
    {
        accessorKey: 'status',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Statut" />
        ),
        cell: ({ row }) => (
            <Badge variant={orderStatusVariant[row.original.status]}>
                {row.original.status_label}
            </Badge>
        ),
    },
    {
        accessorKey: 'payment_status',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Paiement" />
        ),
        cell: ({ row }) => (
            <Badge
                variant={paymentStatusVariant[row.original.payment_status]}
            >
                {row.original.payment_status_label}
            </Badge>
        ),
    },
    {
        accessorKey: 'items_count',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Articles" />
        ),
        cell: ({ row }) => (
            <span className="text-sm tabular-nums">
                {row.original.items_count ?? 0}
            </span>
        ),
    },
    {
        accessorKey: 'total',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Total" />
        ),
        cell: ({ row }) => (
            <span className="text-sm font-medium tabular-nums">
                {row.original.total.toFixed(2)}
            </span>
        ),
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Date" />
        ),
        cell: ({ row }) => (
            <span className="text-sm text-muted-foreground">
                {row.original.created_at}
            </span>
        ),
    },
    {
        id: 'actions',
        cell: ({ row }) => (
            <Button variant="ghost" className="size-8 p-0" asChild>
                <Link href={OrderController.show.url(row.original)}>
                    <span className="sr-only">Voir la commande</span>
                    <EyeIcon className="size-4" />
                </Link>
            </Button>
        ),
    },
];
