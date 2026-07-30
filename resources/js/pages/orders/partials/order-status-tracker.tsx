import {
    CheckIcon,
    HomeIcon,
    PackageIcon,
    ShoppingCartIcon,
    TruckIcon,
    XCircleIcon,
} from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { cn } from '@/lib/utils';
import type { Order, OrderStatus } from '@/types/order';

type Props = {
    order: Order;
};

const STEPS: {
    status: OrderStatus;
    label: string;
    icon: typeof PackageIcon;
}[] = [
    { status: 'pending', label: 'Commande passée', icon: ShoppingCartIcon },
    { status: 'preparing', label: 'En préparation', icon: PackageIcon },
    { status: 'out_for_delivery', label: 'En livraison', icon: TruckIcon },
    { status: 'delivered', label: 'Livrée', icon: HomeIcon },
];

export default function OrderStatusTracker({ order }: Props) {
    if (order.status === 'cancelled') {
        return (
            <Alert variant="destructive">
                <XCircleIcon />
                <AlertTitle>Commande annulée</AlertTitle>
                <AlertDescription>
                    Cette commande a été annulée et ne suit plus le circuit de
                    livraison.
                </AlertDescription>
            </Alert>
        );
    }

    const currentIndex = STEPS.findIndex(
        (step) => step.status === order.status,
    );
    const progressPercent = (currentIndex / (STEPS.length - 1)) * 100;

    return (
        <div className="relative px-2 py-2">
            <div className="absolute top-5 right-7 left-7 h-0.5 bg-border" />
            <div
                className="absolute top-5 left-7 h-0.5 bg-primary transition-all"
                style={{
                    width: `calc((100% - 3.5rem) * ${progressPercent / 100})`,
                }}
            />

            <div className="relative flex justify-between">
                {STEPS.map((step, index) => {
                    const isDone = index < currentIndex;
                    const isCurrent = index === currentIndex;
                    const Icon = step.icon;

                    return (
                        <div
                            key={step.status}
                            className="flex flex-col items-center gap-2"
                        >
                            <div className="relative flex size-10 items-center justify-center">
                                {isCurrent && (
                                    <span className="absolute inset-0 animate-ping rounded-full bg-primary/50" />
                                )}
                                <div
                                    className={cn(
                                        'relative flex size-10 items-center justify-center rounded-full border-2 bg-background',
                                        (isDone || isCurrent) &&
                                            'border-primary',
                                        isDone &&
                                            'bg-primary text-primary-foreground',
                                        isCurrent && 'text-primary',
                                        !isDone &&
                                            !isCurrent &&
                                            'border-border text-muted-foreground',
                                    )}
                                >
                                    {isDone ? (
                                        <CheckIcon className="size-5" />
                                    ) : (
                                        <Icon className="size-5" />
                                    )}
                                </div>
                            </div>
                            <span
                                className={cn(
                                    'max-w-20 text-center text-xs font-medium',
                                    isCurrent
                                        ? 'text-primary'
                                        : isDone
                                          ? 'text-foreground'
                                          : 'text-muted-foreground',
                                )}
                            >
                                {step.label}
                            </span>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
