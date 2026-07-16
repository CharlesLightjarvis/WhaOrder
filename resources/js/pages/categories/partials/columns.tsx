import { useState } from 'react';
import { type ColumnDef } from '@tanstack/react-table';
import { Link, router } from '@inertiajs/react';
import { PencilIcon, Trash2Icon, MoreHorizontalIcon } from 'lucide-react';
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
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';
import type { Category } from '@/types/category';

function RowActions({ category }: { category: Category }) {
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState(false);

    function handleDelete() {
        setDeleting(true);
        router.delete(CategoryController.destroy.url(category), {
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
                            href={CategoryController.edit.url(category)}
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
                        <DialogTitle>Supprimer la catégorie</DialogTitle>
                        <DialogDescription>
                            Êtes-vous sûr de vouloir supprimer{' '}
                            <strong>"{category.name}"</strong> ? Les produits
                            de cette catégorie ne seront pas supprimés, mais
                            ne seront plus catégorisés.
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

export const createColumns = (): ColumnDef<Category>[] => [
    {
        accessorKey: 'name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Nom" />
        ),
        cell: ({ row }) => (
            <span className="font-medium">{row.original.name}</span>
        ),
    },
    {
        accessorKey: 'products_count',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Produits" />
        ),
        cell: ({ row }) => (
            <span className="text-sm tabular-nums">
                {row.original.products_count ?? 0}
            </span>
        ),
    },
    {
        id: 'actions',
        cell: ({ row }) => <RowActions category={row.original} />,
    },
];
