import { Form, Head } from '@inertiajs/react';
import { PlusIcon, Trash2Icon } from 'lucide-react';
import { useState } from 'react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';

type AddressDraft = {
    label: string;
    full_name: string;
    phone: string;
    line1: string;
    line2: string;
    city: string;
    country: string;
    is_default: boolean;
};

const emptyAddress: AddressDraft = {
    label: '',
    full_name: '',
    phone: '',
    line1: '',
    line2: '',
    city: '',
    country: '',
    is_default: false,
};

export default function CustomerCreate() {
    const [addresses, setAddresses] = useState<AddressDraft[]>([]);

    const addAddress = () =>
        setAddresses((prev) => [...prev, { ...emptyAddress }]);
    const removeAddress = (index: number) =>
        setAddresses((prev) => prev.filter((_, i) => i !== index));
    const updateAddress = (
        index: number,
        field: keyof Omit<AddressDraft, 'is_default'>,
        value: string,
    ) =>
        setAddresses((prev) =>
            prev.map((address, i) =>
                i === index ? { ...address, [field]: value } : address,
            ),
        );
    const toggleAddressDefault = (index: number, value: boolean) =>
        setAddresses((prev) =>
            prev.map((address, i) =>
                i === index ? { ...address, is_default: value } : address,
            ),
        );

    return (
        <>
            <Head title="Créer un client" />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-col space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Créer un client
                    </h1>
                    <p className="text-muted-foreground">
                        Ajoutez un client et ses adresses de livraison.
                    </p>
                </div>

                <Separator />

                <Form
                    {...CustomerController.store.form()}
                    className="space-y-8"
                >
                    {({ processing, errors, clearErrors }) => (
                        <>
                            <div className="mx-auto max-w-xl space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="whatsapp_number">
                                        Numéro WhatsApp *
                                    </Label>
                                    <Input
                                        id="whatsapp_number"
                                        name="whatsapp_number"
                                        autoFocus
                                        placeholder="+225 07 00 00 00 00"
                                        onChange={() =>
                                            clearErrors('whatsapp_number')
                                        }
                                    />
                                    <InputError
                                        message={errors.whatsapp_number}
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="name">Nom</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        placeholder="Ex : Awa Koné"
                                        onChange={() => clearErrors('name')}
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="notes">Notes</Label>
                                    <textarea
                                        id="notes"
                                        name="notes"
                                        rows={3}
                                        placeholder="Notes internes sur ce client..."
                                        onChange={() => clearErrors('notes')}
                                        className="flex min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    />
                                    <InputError message={errors.notes} />
                                </div>
                            </div>

                            <Separator />

                            {/* ─── Adresses ─── */}
                            <div className="space-y-4">
                                <h2 className="text-lg font-semibold">
                                    Adresses
                                </h2>

                                {addresses.length === 0 && (
                                    <p className="text-sm text-muted-foreground">
                                        Aucune adresse. Vous pouvez en ajouter
                                        maintenant ou plus tard.
                                    </p>
                                )}

                                <div className="space-y-3">
                                    {addresses.map((address, index) => (
                                        <div
                                            key={index}
                                            className="space-y-3 rounded-lg border border-border/60 bg-muted/30 p-3"
                                        >
                                            <input
                                                type="hidden"
                                                name={`addresses[${index}][is_default]`}
                                                value={
                                                    address.is_default
                                                        ? '1'
                                                        : '0'
                                                }
                                            />

                                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <div className="space-y-1">
                                                    <Label className="text-xs">
                                                        Libellé
                                                    </Label>
                                                    <Input
                                                        name={`addresses[${index}][label]`}
                                                        value={address.label}
                                                        onChange={(e) =>
                                                            updateAddress(
                                                                index,
                                                                'label',
                                                                e.target.value,
                                                            )
                                                        }
                                                        placeholder="Domicile, Bureau..."
                                                    />
                                                </div>
                                                <div className="space-y-1">
                                                    <Label className="text-xs">
                                                        Nom complet
                                                    </Label>
                                                    <Input
                                                        name={`addresses[${index}][full_name]`}
                                                        value={
                                                            address.full_name
                                                        }
                                                        onChange={(e) =>
                                                            updateAddress(
                                                                index,
                                                                'full_name',
                                                                e.target.value,
                                                            )
                                                        }
                                                    />
                                                </div>
                                                <div className="space-y-1">
                                                    <Label className="text-xs">
                                                        Téléphone
                                                    </Label>
                                                    <Input
                                                        name={`addresses[${index}][phone]`}
                                                        value={address.phone}
                                                        onChange={(e) =>
                                                            updateAddress(
                                                                index,
                                                                'phone',
                                                                e.target.value,
                                                            )
                                                        }
                                                    />
                                                </div>
                                                <div className="space-y-1">
                                                    <Label className="text-xs">
                                                        Ville
                                                    </Label>
                                                    <Input
                                                        name={`addresses[${index}][city]`}
                                                        value={address.city}
                                                        onChange={(e) =>
                                                            updateAddress(
                                                                index,
                                                                'city',
                                                                e.target.value,
                                                            )
                                                        }
                                                    />
                                                </div>
                                                <div className="space-y-1 sm:col-span-2">
                                                    <Label className="text-xs">
                                                        Adresse
                                                    </Label>
                                                    <Input
                                                        name={`addresses[${index}][line1]`}
                                                        value={address.line1}
                                                        onChange={(e) =>
                                                            updateAddress(
                                                                index,
                                                                'line1',
                                                                e.target.value,
                                                            )
                                                        }
                                                        placeholder="Rue, quartier..."
                                                    />
                                                </div>
                                                <div className="space-y-1 sm:col-span-2">
                                                    <Label className="text-xs">
                                                        Complément
                                                    </Label>
                                                    <Input
                                                        name={`addresses[${index}][line2]`}
                                                        value={address.line2}
                                                        onChange={(e) =>
                                                            updateAddress(
                                                                index,
                                                                'line2',
                                                                e.target.value,
                                                            )
                                                        }
                                                    />
                                                </div>
                                                <div className="space-y-1">
                                                    <Label className="text-xs">
                                                        Pays (code)
                                                    </Label>
                                                    <Input
                                                        name={`addresses[${index}][country]`}
                                                        value={address.country}
                                                        onChange={(e) =>
                                                            updateAddress(
                                                                index,
                                                                'country',
                                                                e.target.value,
                                                            )
                                                        }
                                                        placeholder="CI, CM..."
                                                        maxLength={2}
                                                    />
                                                </div>
                                                <div className="flex items-end gap-2">
                                                    <Checkbox
                                                        id={`default-${index}`}
                                                        checked={
                                                            address.is_default
                                                        }
                                                        onCheckedChange={(v) =>
                                                            toggleAddressDefault(
                                                                index,
                                                                !!v,
                                                            )
                                                        }
                                                    />
                                                    <Label
                                                        htmlFor={`default-${index}`}
                                                        className="cursor-pointer text-xs font-normal"
                                                    >
                                                        Adresse par défaut
                                                    </Label>
                                                </div>
                                            </div>

                                            <div className="flex justify-end">
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() =>
                                                        removeAddress(index)
                                                    }
                                                >
                                                    <Trash2Icon className="size-4 text-destructive" />
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={addAddress}
                                >
                                    <PlusIcon className="mr-1 size-4" />
                                    Ajouter une adresse
                                </Button>
                            </div>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing}>
                                    {processing && <Spinner className="mr-2" />}
                                    Créer le client
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

CustomerCreate.layout = (page: React.ReactNode) => (
    <AppLayout
        breadcrumbs={[
            { title: 'Dashboard', href: dashboard() },
            { title: 'Clients', href: CustomerController.index.url() },
            {
                title: 'Nouveau client',
                href: CustomerController.create.url(),
            },
        ]}
    >
        {page}
    </AppLayout>
);
