import { useState } from 'react';
import { router } from '@inertiajs/react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import { ORDER_STATUSES } from '@/types/order';
import type { Order, OrderStatus } from '@/types/order';

type Props = {
    order: Order;
};

export default function OrderStatusSelect({ order }: Props) {
    const [updating, setUpdating] = useState(false);

    function handleChange(status: string) {
        setUpdating(true);
        router.patch(
            OrderController.updateStatus.url(order),
            { status: status as OrderStatus },
            { onFinish: () => setUpdating(false), preserveScroll: true },
        );
    }

    return (
        <Select
            value={order.status}
            onValueChange={handleChange}
            disabled={updating}
        >
            <SelectTrigger className="w-[200px]">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                {ORDER_STATUSES.map((status) => (
                    <SelectItem key={status.value} value={status.value}>
                        {status.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
