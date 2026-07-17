<?php

namespace App\Actions\PaymentProofs;

use App\Enums\PaymentProofStatus;
use App\Enums\PaymentStatus;
use App\Models\PaymentProof;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RejectPaymentProof
{
    public function handle(PaymentProof $paymentProof, User $reviewer): PaymentProof
    {
        return DB::transaction(function () use ($paymentProof, $reviewer) {
            $paymentProof->update([
                'status' => PaymentProofStatus::Rejected,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $paymentProof->order->update([
                'payment_status' => PaymentStatus::Failed,
            ]);

            return $paymentProof;
        });
    }
}
