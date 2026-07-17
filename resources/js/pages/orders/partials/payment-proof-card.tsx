import { useState } from 'react';
import { router } from '@inertiajs/react';
import { CheckIcon, XIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import PaymentProofController from '@/actions/App/Http/Controllers/PaymentProofController';
import type { PaymentProof, PaymentProofStatus } from '@/types/order';

type Props = {
    paymentProof: PaymentProof;
};

const statusVariant: Record<
    PaymentProofStatus,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    pending_review: 'outline',
    confirmed: 'default',
    rejected: 'destructive',
};

export default function PaymentProofCard({ paymentProof }: Props) {
    const [processing, setProcessing] = useState(false);

    function handleConfirm() {
        setProcessing(true);
        router.patch(
            PaymentProofController.confirm.url(paymentProof),
            {},
            { onFinish: () => setProcessing(false), preserveScroll: true },
        );
    }

    function handleReject() {
        setProcessing(true);
        router.patch(
            PaymentProofController.reject.url(paymentProof),
            {},
            { onFinish: () => setProcessing(false), preserveScroll: true },
        );
    }

    return (
        <div className="space-y-3 rounded-lg border p-4">
            <div className="flex items-center justify-between">
                <span className="text-sm font-medium">
                    {paymentProof.type_label}
                </span>
                <Badge variant={statusVariant[paymentProof.status]}>
                    {paymentProof.status_label}
                </Badge>
            </div>

            {paymentProof.image_url && (
                <img
                    src={paymentProof.image_url}
                    alt="Preuve de paiement"
                    className="max-h-64 w-full rounded-md border object-contain"
                />
            )}

            {paymentProof.raw_message && (
                <p className="rounded-md bg-muted p-3 text-sm text-muted-foreground">
                    {paymentProof.raw_message}
                </p>
            )}

            {paymentProof.status === 'pending_review' && (
                <div className="flex items-center gap-2">
                    <Button
                        size="sm"
                        onClick={handleConfirm}
                        disabled={processing}
                    >
                        <CheckIcon className="size-4" />
                        Confirmer ce paiement
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        onClick={handleReject}
                        disabled={processing}
                    >
                        <XIcon className="size-4" />
                        Rejeter
                    </Button>
                </div>
            )}
        </div>
    );
}
