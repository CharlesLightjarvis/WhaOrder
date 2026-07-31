import { Head, usePage } from '@inertiajs/react';
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { Category } from '@/types/category';
import type { Paginated } from '@/types/pagination';
import CategoryList from './partials/category-list';

type Props = {
    categories: Paginated<Category>;
};

export default function CategoryIndex() {
    const { categories } = usePage<Props>().props;

    return (
        <>
            <Head title="Catégories" />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-col space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Gestion des catégories
                    </h1>
                    <p className="text-muted-foreground">
                        Organisez votre catalogue en catégories.
                    </p>
                </div>

                <CategoryList categories={categories} />
            </div>
        </>
    );
}

CategoryIndex.layout = (page: React.ReactNode) => (
    <AppLayout
        breadcrumbs={[
            { title: 'Dashboard', href: dashboard() },
            { title: 'Catégories', href: CategoryController.index.url() },
        ]}
    >
        {page}
    </AppLayout>
);
