import { Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { PencilIcon, Trash2Icon, MoreHorizontalIcon } from 'lucide-react';
import { useState } from 'react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import { DataTableColumnHeader } from '@/components/data-table-column-header';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Customer } from '@/types/customer';

function RowActions({ customer }: { customer: Customer }) {
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState(false);

    function handleDelete() {
        setDeleting(true);
        router.delete(CustomerController.destroy.url(customer), {
            onFinish: () => {
                setDeleting(false);
                setDeleteOpen(false);
            },
        });
    }

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" className="size-8 p-0">
                        <span className="sr-only">Actions</span>
                        <MoreHorizontalIcon className="size-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem asChild>
                        <Link
                            href={CustomerController.edit.url(customer)}
                            className="flex items-center gap-2"
                        >
                            <PencilIcon className="size-4" />
                            Modifier
                        </Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                        onClick={() => setDeleteOpen(true)}
                        className="flex items-center gap-2 text-destructive focus:text-destructive"
                    >
                        <Trash2Icon className="size-4" />
                        Supprimer
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <Dialog open={deleteOpen} onOpenChange={setDeleteOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Supprimer le client</DialogTitle>
                        <DialogDescription>
                            Êtes-vous sûr de vouloir supprimer{' '}
                            <strong>
                                "{customer.name ?? customer.whatsapp_number}"
                            </strong>{' '}
                            ? Cette action est irréversible.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setDeleteOpen(false)}
                            disabled={deleting}
                        >
                            Annuler
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            onClick={handleDelete}
                            disabled={deleting}
                        >
                            {deleting ? 'Suppression…' : 'Supprimer'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

export const createColumns = (): ColumnDef<Customer>[] => [
    {
        accessorKey: 'name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Client" />
        ),
        cell: ({ row }) => (
            <span className="font-medium">
                {row.original.name ?? (
                    <span className="text-muted-foreground italic">
                        Sans nom
                    </span>
                )}
            </span>
        ),
    },
    {
        accessorKey: 'whatsapp_number',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="WhatsApp" />
        ),
        cell: ({ row }) => (
            <span className="text-sm tabular-nums">
                {row.original.whatsapp_number}
            </span>
        ),
    },
    {
        accessorKey: 'addresses_count',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Adresses" />
        ),
        cell: ({ row }) => (
            <span className="text-sm tabular-nums">
                {row.original.addresses_count ?? 0}
            </span>
        ),
    },
    {
        accessorKey: 'last_order_at',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Dernière commande" />
        ),
        cell: ({ row }) => (
            <span className="text-sm text-muted-foreground">
                {row.original.last_order_at ?? '—'}
            </span>
        ),
    },
    {
        id: 'actions',
        cell: ({ row }) => <RowActions customer={row.original} />,
    },
];
