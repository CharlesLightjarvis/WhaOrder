import { router } from '@inertiajs/react';
import { useMemo } from 'react';
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import type { Category } from '@/types/category';
import type { Paginated } from '@/types/pagination';
import { createColumns } from './columns';

type Props = {
    categories: Paginated<Category>;
};

export default function CategoryList({ categories }: Props) {
    const columns = useMemo(() => createColumns(), []);

    const prevUrl = categories.links[0]?.url;
    const nextUrl = categories.links[categories.links.length - 1]?.url;

    return (
        <div className="space-y-4">
            <DataTable
                columns={columns}
                data={categories.data}
                searchFilter={{
                    columnIds: ['name'],
                    placeholder: 'Rechercher une catégorie...',
                }}
                actionButton={{
                    label: 'Créer une catégorie',
                    onClick: () =>
                        router.visit(CategoryController.create.url()),
                }}
            />

            {categories.last_page > 1 && (
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
                        Page {categories.current_page} / {categories.last_page}
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
