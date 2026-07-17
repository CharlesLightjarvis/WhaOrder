import { type ColumnDef } from '@tanstack/react-table';
import { Link } from '@inertiajs/react';
import { EyeIcon } from 'lucide-react';
import { DataTableColumnHeader } from '@/components/data-table-column-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import ConversationController from '@/actions/App/Http/Controllers/ConversationController';
import type { Conversation, ConversationStatus } from '@/types/conversation';

const statusVariant: Record<
    ConversationStatus,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    active: 'default',
    completed: 'secondary',
    abandoned: 'outline',
};

export const createColumns = (): ColumnDef<Conversation>[] => [
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
            <Badge variant={statusVariant[row.original.status]}>
                {row.original.status_label}
            </Badge>
        ),
    },
    {
        accessorKey: 'last_message_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Dernier message" />
        ),
        cell: ({ row }) => (
            <span className="text-sm text-muted-foreground">
                {row.original.last_message_at ?? '—'}
            </span>
        ),
    },
    {
        id: 'actions',
        cell: ({ row }) => (
            <Button variant="ghost" className="size-8 p-0" asChild>
                <Link href={ConversationController.show.url(row.original)}>
                    <span className="sr-only">Voir la conversation</span>
                    <EyeIcon className="size-4" />
                </Link>
            </Button>
        ),
    },
];
