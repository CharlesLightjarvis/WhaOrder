import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import OrderList from './partials/order-list';
import type { Paginated } from '@/types/pagination';
import type { Order } from '@/types/order';

type Props = {
    orders: Paginated<Order>;
};

export default function OrderIndex() {
    const { orders } = usePage<Props>().props;

    return (
        <>
            <Head title="Commandes" />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-col space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Commandes
                    </h1>
                    <p className="text-muted-foreground">
                        Suivez les commandes passées sur WhatsApp et gérez
                        leur statut.
                    </p>
                </div>

                <OrderList orders={orders} />
            </div>
        </>
    );
}

OrderIndex.layout = (page: React.ReactNode) => (
    <AppLayout
        breadcrumbs={[
            { title: 'Dashboard', href: dashboard() },
            { title: 'Commandes', href: OrderController.index.url() },
        ]}
    >
        {page}
    </AppLayout>
);
