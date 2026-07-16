import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import ProductList from './partials/product-list';
import type { Paginated } from '@/types/pagination';
import type { Product } from '@/types/product';

type Props = {
    products: Paginated<Product>;
};

export default function ProductIndex() {
    const { products } = usePage<Props>().props;

    return (
        <>
            <Head title="Produits" />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-col space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Gestion des produits
                    </h1>
                    <p className="text-muted-foreground">
                        Créez, modifiez et gérez votre catalogue de produits.
                    </p>
                </div>

                <ProductList products={products} />
            </div>
        </>
    );
}

ProductIndex.layout = (page: React.ReactNode) => (
    <AppLayout
        breadcrumbs={[
            { title: 'Dashboard', href: dashboard() },
            { title: 'Produits', href: ProductController.index.url() },
        ]}
    >
        {page}
    </AppLayout>
);
