import { Form, Head, useHttp, usePage } from '@inertiajs/react';
import { LocateFixedIcon } from 'lucide-react';
import { useState } from 'react';
import MerchantController from '@/actions/App/Http/Controllers/Settings/MerchantController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { edit as editMerchant } from '@/routes/merchant';
import { TIMEZONES } from '@/types/merchant';
import type { Merchant } from '@/types/merchant';

type Props = {
    merchant: Merchant;
    currencies: Record<string, string>;
};

type DetectedLocation = {
    currency: string;
    timezone: string;
};

export default function MerchantSettings() {
    const { merchant, currencies } = usePage<Props>().props;

    const [currency, setCurrency] = useState(merchant.currency);
    const [timezone, setTimezone] = useState(merchant.timezone);

    const { get, processing: detecting } = useHttp<
        Record<string, never>,
        DetectedLocation
    >();

    async function handleDetect() {
        const location = await get(MerchantController.detectLocation.url());
        setCurrency(location.currency);
        setTimezone(location.timezone);
    }

    return (
        <>
            <Head title="Business settings" />

            <h1 className="sr-only">Business settings</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Business"
                    description="Update your shop's name, notification number, currency, and timezone"
                />

                <Form
                    {...MerchantController.update.form()}
                    options={{ preserveScroll: true }}
                    className="space-y-6"
                >
                    {({ processing, errors, clearErrors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Business name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={merchant.name}
                                    onChange={() => clearErrors('name')}
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="whatsapp_admin_number">
                                    Admin WhatsApp number (order notifications)
                                </Label>
                                <Input
                                    id="whatsapp_admin_number"
                                    name="whatsapp_admin_number"
                                    defaultValue={
                                        merchant.whatsapp_admin_number ?? ''
                                    }
                                    placeholder="+225 07 00 00 00 00"
                                    onChange={() =>
                                        clearErrors('whatsapp_admin_number')
                                    }
                                />
                                <InputError
                                    message={errors.whatsapp_admin_number}
                                />
                            </div>

                            <div className="flex items-center justify-between gap-4">
                                <p className="text-sm text-muted-foreground">
                                    Currency and timezone are prefilled from
                                    your location — adjust them if needed.
                                </p>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={handleDetect}
                                    disabled={detecting}
                                >
                                    {detecting ? (
                                        <Spinner className="mr-1" />
                                    ) : (
                                        <LocateFixedIcon className="mr-1 size-4" />
                                    )}
                                    Detect automatically
                                </Button>
                            </div>

                            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label>Currency</Label>
                                    <Select
                                        value={currency}
                                        onValueChange={(v) => {
                                            setCurrency(v);
                                            clearErrors('currency');
                                        }}
                                    >
                                        <SelectTrigger className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {Object.entries(currencies).map(
                                                ([code, label]) => (
                                                    <SelectItem
                                                        key={code}
                                                        value={code}
                                                    >
                                                        {label}
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                    <input
                                        type="hidden"
                                        name="currency"
                                        value={currency}
                                    />
                                    <InputError message={errors.currency} />
                                </div>

                                <div className="space-y-2">
                                    <Label>Timezone</Label>
                                    <Select
                                        value={timezone}
                                        onValueChange={(v) => {
                                            setTimezone(v);
                                            clearErrors('timezone');
                                        }}
                                    >
                                        <SelectTrigger className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {TIMEZONES.map((tz) => (
                                                <SelectItem
                                                    key={tz.value}
                                                    value={tz.value}
                                                >
                                                    {tz.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <input
                                        type="hidden"
                                        name="timezone"
                                        value={timezone}
                                    />
                                    <InputError message={errors.timezone} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="delivery_fee">
                                    Delivery fee
                                </Label>
                                <Input
                                    id="delivery_fee"
                                    name="delivery_fee"
                                    type="number"
                                    min={0}
                                    step={0.01}
                                    defaultValue={merchant.delivery_fee}
                                    onChange={() => clearErrors('delivery_fee')}
                                />
                                <p className="text-sm text-muted-foreground">
                                    Flat fee applied to every order, regardless
                                    of the delivery city.
                                </p>
                                <InputError message={errors.delivery_fee} />
                            </div>

                            <Button type="submit" disabled={processing}>
                                {processing && <Spinner className="mr-2" />}
                                Save
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

MerchantSettings.layout = {
    breadcrumbs: [
        {
            title: 'Business settings',
            href: editMerchant(),
        },
    ],
};
