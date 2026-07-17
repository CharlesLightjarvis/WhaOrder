import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import PaymentProofCard from './partials/payment-proof-card';
import type { Order } from '@/types/order';

type Props = {
    order: Order;
};

export default function OrderShow() {
    const { order } = usePage<Props>().props;

    return (
        <>
            <Head title={`Commande #${order.id.slice(0, 8)}`} />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div className="flex flex-col space-y-1">
                        <h1 className="text-3xl font-bold tracking-tight">
                            Commande #{order.id.slice(0, 8)}
                        </h1>
                        <p className="text-muted-foreground">
                            Passée le {order.created_at}
                        </p>
                    </div>
                    <OrderStatusSelect order={order} />
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Articles</CardTitle>
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
                                                        {
                                                            item.product_name_snapshot
                                                        }
                                                    </div>
                                                    {item.variant_name_snapshot && (
                                                        <div className="text-sm text-muted-foreground">
                                                            {
                                                                item.variant_name_snapshot
                                                            }
                                                        </div>
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-right tabular-nums">
                                                    {item.quantity}
                                                </TableCell>
                                                <TableCell className="text-right tabular-nums">
                                                    {item.unit_price.toFixed(
                                                        2,
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-right font-medium tabular-nums">
                                                    {item.line_total.toFixed(
                                                        2,
                                                    )}
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
                                                {order.delivery_fee.toFixed(
                                                    2,
                                                )}
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

                        {order.payment_proofs &&
                            order.payment_proofs.length > 0 && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>
                                            Preuves de paiement
                                        </CardTitle>
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

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Client</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-1">
                                <p className="font-medium">
                                    {order.customer?.name ?? 'Sans nom'}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {order.customer?.whatsapp_number}
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Paiement</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <div className="flex items-center justify-between text-sm">
                                    <span className="text-muted-foreground">
                                        Statut
                                    </span>
                                    <Badge variant="outline">
                                        {order.payment_status_label}
                                    </Badge>
                                </div>
                                {order.payment_method_label && (
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="text-muted-foreground">
                                            Méthode
                                        </span>
                                        <span>
                                            {order.payment_method_label}
                                        </span>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Livraison</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm">
                                {order.delivery ? (
                                    <>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">
                                                Statut
                                            </span>
                                            <Badge variant="outline">
                                                {order.delivery.status_label}
                                            </Badge>
                                        </div>
                                        {order.delivery.address_text && (
                                            <p>
                                                {order.delivery.address_text}
                                            </p>
                                        )}
                                        {order.delivery.city && (
                                            <p className="text-muted-foreground">
                                                {order.delivery.city}
                                            </p>
                                        )}
                                    </>
                                ) : (
                                    <>
                                        {order.delivery_address_text && (
                                            <p>
                                                {order.delivery_address_text}
                                            </p>
                                        )}
                                        {order.delivery_city && (
                                            <p className="text-muted-foreground">
                                                {order.delivery_city}
                                            </p>
                                        )}
                                        {!order.delivery_address_text &&
                                            !order.delivery_city && (
                                                <p className="text-muted-foreground italic">
                                                    Pas encore de livraison
                                                    planifiée.
                                                </p>
                                            )}
                                    </>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
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
