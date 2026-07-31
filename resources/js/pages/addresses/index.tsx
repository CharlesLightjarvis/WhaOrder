import { Head, usePage } from '@inertiajs/react';
import AddressController from '@/actions/App/Http/Controllers/AddressController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { Address } from '@/types/address';
import type { Paginated } from '@/types/pagination';
import AddressList from './partials/address-list';

type Props = {
    addresses: Paginated<Address>;
};

export default function AddressIndex() {
    const { addresses } = usePage<Props>().props;

    return (
        <>
            <Head title="Adresses" />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-col space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Gestion des adresses
                    </h1>
                    <p className="text-muted-foreground">
                        Consultez et gérez les adresses de livraison de vos
                        clients.
                    </p>
                </div>

                <AddressList addresses={addresses} />
            </div>
        </>
    );
}

AddressIndex.layout = (page: React.ReactNode) => (
    <AppLayout
        breadcrumbs={[
            { title: 'Dashboard', href: dashboard() },
            { title: 'Adresses', href: AddressController.index.url() },
        ]}
    >
        {page}
    </AppLayout>
);
