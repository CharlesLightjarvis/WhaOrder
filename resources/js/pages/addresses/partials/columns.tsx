import { useState } from 'react';
import { type ColumnDef } from '@tanstack/react-table';
import { Link, router } from '@inertiajs/react';
import { PencilIcon, Trash2Icon, MoreHorizontalIcon } from 'lucide-react';
import { DataTableColumnHeader } from '@/components/data-table-column-header';
import { Badge } from '@/components/ui/badge';
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
import AddressController from '@/actions/App/Http/Controllers/AddressController';
import type { Address } from '@/types/address';

function RowActions({ address }: { address: Address }) {
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState(false);

    function handleDelete() {
        setDeleting(true);
        router.delete(AddressController.destroy.url(address), {
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
                            href={AddressController.edit.url(address)}
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
                        <DialogTitle>Supprimer l'adresse</DialogTitle>
                        <DialogDescription>
                            Êtes-vous sûr de vouloir supprimer cette adresse ?
                            Cette action est irréversible.
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

export const createColumns = (): ColumnDef<Address>[] => [
    {
        id: 'customer',
        accessorFn: (row) =>
            row.customer?.name ?? row.customer?.whatsapp_number ?? '',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Client" />
        ),
        cell: ({ row }) => (
            <span className="font-medium">
                {row.original.customer?.name ??
                    row.original.customer?.whatsapp_number ??
                    '—'}
            </span>
        ),
    },
    {
        accessorKey: 'city',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Ville" />
        ),
        cell: ({ row }) => (
            <span className="text-sm text-muted-foreground">
                {row.original.city ?? '—'}
            </span>
        ),
    },
    {
        accessorKey: 'line1',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Adresse" />
        ),
        cell: ({ row }) => {
            const address = row.original;

            return (
                <div className="flex items-center gap-2">
                    <span className="text-sm text-muted-foreground">
                        {address.line1 ?? '—'}
                    </span>
                    {address.is_default && (
                        <Badge variant="default" className="shrink-0">
                            Par défaut
                        </Badge>
                    )}
                </div>
            );
        },
    },
    {
        id: 'actions',
        cell: ({ row }) => <RowActions address={row.original} />,
    },
];
