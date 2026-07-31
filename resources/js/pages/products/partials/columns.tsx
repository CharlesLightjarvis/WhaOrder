import { Link, router } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import {
    ImageIcon,
    PencilIcon,
    Trash2Icon,
    MoreHorizontalIcon,
    EyeIcon,
    EyeOffIcon,
} from 'lucide-react';
import { useState } from 'react';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
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
import type { Product } from '@/types/product';

function RowActions({ product }: { product: Product }) {
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [toggling, setToggling] = useState(false);

    function handleToggleActive() {
        setToggling(true);
        router.patch(
            ProductController.toggleActive.url(product),
            {},
            { onFinish: () => setToggling(false) },
        );
    }

    function handleDelete() {
        setDeleting(true);
        router.delete(ProductController.destroy.url(product), {
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
                            href={ProductController.edit.url(product)}
                            className="flex items-center gap-2"
                        >
                            <PencilIcon className="size-4" />
                            Modifier
                        </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        onClick={handleToggleActive}
                        disabled={toggling}
                        className="flex items-center gap-2"
                    >
                        {product.is_active ? (
                            <>
                                <EyeOffIcon className="size-4" />
                                Désactiver
                            </>
                        ) : (
                            <>
                                <EyeIcon className="size-4" />
                                Activer
                            </>
                        )}
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
                        <DialogTitle>Supprimer le produit</DialogTitle>
                        <DialogDescription>
                            Êtes-vous sûr de vouloir supprimer{' '}
                            <strong>"{product.name}"</strong> ? Cette action est
                            irréversible.
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

export const createColumns = (): ColumnDef<Product>[] => [
    {
        accessorKey: 'name',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Produit" />
        ),
        cell: ({ row }) => {
            const product = row.original;
            const image = product.images?.[0];

            return (
                <div className="flex items-center gap-3">
                    {image ? (
                        <img
                            src={image.url}
                            alt={product.name}
                            className="size-9 shrink-0 rounded-md object-cover"
                        />
                    ) : (
                        <div className="flex size-9 shrink-0 items-center justify-center rounded-md bg-muted">
                            <ImageIcon className="size-4 text-muted-foreground" />
                        </div>
                    )}
                    <span className="font-medium">{product.name}</span>
                </div>
            );
        },
    },
    {
        id: 'category',
        accessorFn: (row) => row.category?.name ?? '',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Catégorie" />
        ),
        cell: ({ row }) => (
            <span className="text-sm text-muted-foreground">
                {row.original.category?.name ?? '—'}
            </span>
        ),
    },
    {
        accessorKey: 'variants_count',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Variantes" />
        ),
        cell: ({ row }) => (
            <span className="text-sm tabular-nums">
                {row.original.variants_count ?? 0}
            </span>
        ),
    },
    {
        accessorKey: 'stock',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Stock" />
        ),
        cell: ({ row }) => (
            <span className="text-sm tabular-nums">
                {row.getValue<number>('stock')}
            </span>
        ),
    },
    {
        accessorKey: 'price',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Prix" />
        ),
        cell: ({ row }) => (
            <span className="text-sm tabular-nums">
                {row.getValue<number>('price')}
            </span>
        ),
    },
    {
        accessorKey: 'is_active',
        header: ({ column }) => (
            <DataTableColumnHeader column={column} title="Statut" />
        ),
        filterFn: (row, id, value: string[]) =>
            value.includes(String(row.getValue(id))),
        cell: ({ row }) =>
            row.getValue('is_active') ? (
                <Badge
                    variant="outline"
                    className="bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400"
                >
                    Actif
                </Badge>
            ) : (
                <Badge
                    variant="outline"
                    className="bg-muted text-muted-foreground"
                >
                    Inactif
                </Badge>
            ),
    },
    {
        id: 'actions',
        cell: ({ row }) => <RowActions product={row.original} />,
    },
];
