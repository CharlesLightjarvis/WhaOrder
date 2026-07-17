<?php

namespace App\Actions\PaymentProofs;

use App\Enums\PaymentProofStatus;
use App\Enums\PaymentStatus;
use App\Models\PaymentProof;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConfirmPaymentProof
{
    public function handle(PaymentProof $paymentProof, User $reviewer): PaymentProof
    {
        return DB::transaction(function () use ($paymentProof, $reviewer) {
            $paymentProof->update([
                'status' => PaymentProofStatus::Confirmed,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $paymentProof->order->update([
                'payment_status' => PaymentStatus::Confirmed,
            ]);

            return $paymentProof;
        });
    }
}
