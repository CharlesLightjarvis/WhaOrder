import { Head, usePage } from '@inertiajs/react';
import { CreditCardIcon, MapPinIcon, UserIcon } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import OrderStatusSelect from './partials/order-status-select';
import OrderStatusTracker from './partials/order-status-tracker';
import PaymentProofCard from './partials/payment-proof-card';
import type { DeliveryStatus, Order, PaymentStatus } from '@/types/order';

type Props = {
    order: Order;
};

type BadgeVariant = 'default' | 'secondary' | 'destructive' | 'outline';

const paymentStatusVariant: Record<PaymentStatus, BadgeVariant> = {
    unpaid: 'outline',
    claimed: 'secondary',
    confirmed: 'default',
    failed: 'destructive',
};

const deliveryStatusVariant: Record<DeliveryStatus, BadgeVariant> = {
    pending: 'outline',
    out_for_delivery: 'secondary',
    delivered: 'default',
    failed: 'destructive',
};

export default function OrderShow() {
    const { order } = usePage<Props>().props;

    return (
        <>
            <Head title={`Commande #${order.id.slice(0, 8)}`} />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div className="flex flex-col space-y-1">
                        <h1 className="text-3xl font-bold tracking-tight">
                            Commande #{order.id.slice(0, 8)}
                        </h1>
                        <p className="text-muted-foreground">
                            Passée le {order.created_at}
                        </p>
                        <div className="pt-1">
                            <OrderStatusSelect order={order} />
                        </div>
                    </div>
                    <div className="text-right">
                        <span className="block text-xs tracking-wide text-muted-foreground uppercase">
                            Montant total
                        </span>
                        <span className="text-3xl font-bold text-primary">
                            {order.total.toFixed(2)}
                        </span>
                    </div>
                </div>

                <Card>
                    <CardContent className="flex flex-col justify-between gap-6 sm:flex-row sm:flex-wrap">
                        <div className="flex items-start gap-3">
                            <UserIcon className="mt-0.5 size-4 shrink-0 text-primary" />
                            <div>
                                <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    Client
                                </p>
                                <div className="mt-1 flex items-center gap-2">
                                    <span className="font-medium">
                                        {order.customer?.name ?? 'Sans nom'}
                                    </span>
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    {order.customer?.whatsapp_number}
                                </p>
                            </div>
                        </div>

                        <div className="flex items-start gap-3 sm:border-l sm:border-border/60 sm:pl-6">
                            <CreditCardIcon className="mt-0.5 size-4 shrink-0 text-primary" />
                            <div className="space-y-1">
                                <div className="flex items-center gap-2">
                                    <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                        Paiement
                                    </p>
                                    <Badge
                                        variant={
                                            paymentStatusVariant[
                                                order.payment_status
                                            ]
                                        }
                                    >
                                        {order.payment_status_label}
                                    </Badge>
                                </div>
                                {order.payment_method_label && (
                                    <p className="text-sm text-muted-foreground">
                                        {order.payment_method_label}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="flex items-start gap-3 sm:border-l sm:border-border/60 sm:pl-6">
                            <MapPinIcon className="mt-0.5 size-4 shrink-0 text-primary" />
                            <div className="space-y-1">
                                <div className="flex items-center gap-2">
                                    <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                        Livraison
                                    </p>
                                    {order.delivery && (
                                        <Badge
                                            variant={
                                                deliveryStatusVariant[
                                                    order.delivery.status
                                                ]
                                            }
                                        >
                                            {order.delivery.status_label}
                                        </Badge>
                                    )}
                                </div>
                                {(() => {
                                    const address =
                                        order.delivery?.address_text ??
                                        order.delivery_address_text;
                                    const city =
                                        order.delivery?.city ??
                                        order.delivery_city;

                                    if (!address && !city) {
                                        return (
                                            <p className="text-sm text-muted-foreground italic">
                                                Pas encore planifiée.
                                            </p>
                                        );
                                    }

                                    return (
                                        <p className="text-sm">
                                            {address}
                                            {address && city && ', '}
                                            <span className="text-muted-foreground">
                                                {city}
                                            </span>
                                        </p>
                                    );
                                })()}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="py-2">
                        <OrderStatusTracker order={order} />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex-row items-center justify-between">
                        <CardTitle>Articles</CardTitle>
                        <Badge variant="secondary">
                            {order.items?.length ?? 0} article(s)
                        </Badge>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Produit</TableHead>
                                    <TableHead className="text-right">
                                        Qté
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Prix unitaire
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Total
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {order.items?.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell>
                                            <div className="font-medium">
                                                {item.product_name_snapshot}
                                            </div>
                                            {item.variant_name_snapshot && (
                                                <div className="text-sm text-muted-foreground">
                                                    {item.variant_name_snapshot}
                                                </div>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {item.quantity}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {item.unit_price.toFixed(2)}
                                        </TableCell>
                                        <TableCell className="text-right font-medium tabular-nums">
                                            {item.line_total.toFixed(2)}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                            <TableFooter>
                                <TableRow>
                                    <TableCell colSpan={3}>
                                        Sous-total
                                    </TableCell>
                                    <TableCell className="text-right tabular-nums">
                                        {order.subtotal.toFixed(2)}
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell colSpan={3}>
                                        Frais de livraison
                                    </TableCell>
                                    <TableCell className="text-right tabular-nums">
                                        {order.delivery_fee.toFixed(2)}
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell
                                        colSpan={3}
                                        className="font-semibold"
                                    >
                                        Total
                                    </TableCell>
                                    <TableCell className="text-right font-semibold tabular-nums">
                                        {order.total.toFixed(2)}
                                    </TableCell>
                                </TableRow>
                            </TableFooter>
                        </Table>
                    </CardContent>
                </Card>

                {order.payment_proofs && order.payment_proofs.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Preuves de paiement</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {order.payment_proofs.map((proof) => (
                                <PaymentProofCard
                                    key={proof.id}
                                    paymentProof={proof}
                                />
                            ))}
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}

OrderShow.layout = (page: React.ReactNode) => (
    <OrderShowLayout>{page}</OrderShowLayout>
);

function OrderShowLayout({ children }: { children: React.ReactNode }) {
    const { order } = usePage<Props>().props;
    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Dashboard', href: dashboard() },
                { title: 'Commandes', href: OrderController.index.url() },
                { title: `#${order.id.slice(0, 8)}`, href: '#' },
            ]}
        >
            {children}
        </AppLayout>
    );
}
