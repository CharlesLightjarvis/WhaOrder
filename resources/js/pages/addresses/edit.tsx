import { Form, Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AddressController from '@/actions/App/Http/Controllers/AddressController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { Address, AddressCustomer } from '@/types/address';

type PageProps = {
    address: Address;
    customers: AddressCustomer[];
};

export default function AddressEdit() {
    const { address, customers } = usePage<PageProps>().props;

    const [customerId, setCustomerId] = useState(
        address.customer ? String(address.customer.id) : '',
    );
    const [isDefault, setIsDefault] = useState(address.is_default);

    return (
        <>
            <Head title="Modifier l'adresse" />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-col space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Modifier l'adresse
                    </h1>
                    <p className="text-muted-foreground">
                        Modifiez les informations de cette adresse.
                    </p>
                </div>

                <Separator />

                <Form
                    {...AddressController.update.form(address)}
                    className="mx-auto max-w-xl space-y-6"
                >
                    {({ processing, errors, clearErrors }) => (
                        <>
                            <input
                                type="hidden"
                                name="customer_id"
                                value={customerId}
                            />
                            <input
                                type="hidden"
                                name="is_default"
                                value={isDefault ? '1' : '0'}
                            />

                            <div className="space-y-2">
                                <Label>Client *</Label>
                                <Select
                                    value={customerId}
                                    onValueChange={(v) => {
                                        setCustomerId(v);
                                        clearErrors('customer_id');
                                    }}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Choisir un client" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {customers.map((customer) => (
                                            <SelectItem
                                                key={customer.id}
                                                value={String(customer.id)}
                                            >
                                                {customer.name ??
                                                    customer.whatsapp_number}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.customer_id} />
                            </div>

                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="label">Libellé</Label>
                                    <Input
                                        id="label"
                                        name="label"
                                        defaultValue={address.label ?? ''}
                                        placeholder="Domicile, Bureau..."
                                        onChange={() => clearErrors('label')}
                                    />
                                    <InputError message={errors.label} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="full_name">
                                        Nom complet
                                    </Label>
                                    <Input
                                        id="full_name"
                                        name="full_name"
                                        defaultValue={address.full_name ?? ''}
                                        onChange={() =>
                                            clearErrors('full_name')
                                        }
                                    />
                                    <InputError message={errors.full_name} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="phone">Téléphone</Label>
                                    <Input
                                        id="phone"
                                        name="phone"
                                        defaultValue={address.phone ?? ''}
                                        onChange={() => clearErrors('phone')}
                                    />
                                    <InputError message={errors.phone} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="city">Ville</Label>
                                    <Input
                                        id="city"
                                        name="city"
                                        defaultValue={address.city ?? ''}
                                        onChange={() => clearErrors('city')}
                                    />
                                    <InputError message={errors.city} />
                                </div>

                                <div className="space-y-2 sm:col-span-2">
                                    <Label htmlFor="line1">Adresse</Label>
                                    <Input
                                        id="line1"
                                        name="line1"
                                        defaultValue={address.line1 ?? ''}
                                        placeholder="Rue, quartier..."
                                        onChange={() => clearErrors('line1')}
                                    />
                                    <InputError message={errors.line1} />
                                </div>

                                <div className="space-y-2 sm:col-span-2">
                                    <Label htmlFor="line2">Complément</Label>
                                    <Input
                                        id="line2"
                                        name="line2"
                                        defaultValue={address.line2 ?? ''}
                                        onChange={() => clearErrors('line2')}
                                    />
                                    <InputError message={errors.line2} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="country">Pays (code)</Label>
                                    <Input
                                        id="country"
                                        name="country"
                                        defaultValue={address.country ?? ''}
                                        placeholder="CI, CM..."
                                        maxLength={2}
                                        onChange={() => clearErrors('country')}
                                    />
                                    <InputError message={errors.country} />
                                </div>

                                <div className="flex items-end gap-2">
                                    <Checkbox
                                        id="is_default"
                                        checked={isDefault}
                                        onCheckedChange={(v) =>
                                            setIsDefault(!!v)
                                        }
                                    />
                                    <Label
                                        htmlFor="is_default"
                                        className="cursor-pointer text-sm font-normal"
                                    >
                                        Adresse par défaut
                                    </Label>
                                </div>
                            </div>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing}>
                                    {processing && <Spinner className="mr-2" />}
                                    Enregistrer les modifications
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

AddressEdit.layout = (page: React.ReactNode) => (
    <AppLayout
        breadcrumbs={[
            { title: 'Dashboard', href: dashboard() },
            { title: 'Adresses', href: AddressController.index.url() },
            { title: 'Modifier', href: '#' },
        ]}
    >
        {page}
    </AppLayout>
);
