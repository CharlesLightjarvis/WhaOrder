import { Head, usePage } from '@inertiajs/react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { Customer } from '@/types/customer';
import type { Paginated } from '@/types/pagination';
import CustomerList from './partials/customer-list';

type Props = {
    customers: Paginated<Customer>;
};

export default function CustomerIndex() {
    const { customers } = usePage<Props>().props;

    return (
        <>
            <Head title="Clients" />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-col space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Gestion des clients
                    </h1>
                    <p className="text-muted-foreground">
                        Consultez et gérez les clients qui vous écrivent sur
                        WhatsApp.
                    </p>
                </div>

                <CustomerList customers={customers} />
            </div>
        </>
    );
}

CustomerIndex.layout = (page: React.ReactNode) => (
    <AppLayout
        breadcrumbs={[
            { title: 'Dashboard', href: dashboard() },
            { title: 'Clients', href: CustomerController.index.url() },
        ]}
    >
        {page}
    </AppLayout>
);
