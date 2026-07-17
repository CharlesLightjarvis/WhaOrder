<?php

namespace App\Http\Controllers;

use App\Actions\PaymentProofs\ConfirmPaymentProof;
use App\Actions\PaymentProofs\RejectPaymentProof;
use App\Models\PaymentProof;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentProofController extends Controller
{
    public function __construct(
        private readonly ConfirmPaymentProof $confirmPaymentProof,
        private readonly RejectPaymentProof $rejectPaymentProof,
    ) {}

    /**
     * Confirm the specified payment proof.
     */
    public function confirm(Request $request, PaymentProof $paymentProof): RedirectResponse
    {
        $this->confirmPaymentProof->handle($paymentProof, $request->user());

        return back()->with('success', 'Paiement confirmé.');
    }

    /**
     * Reject the specified payment proof.
     */
    public function reject(Request $request, PaymentProof $paymentProof): RedirectResponse
    {
        $this->rejectPaymentProof->handle($paymentProof, $request->user());

        return back()->with('success', 'Paiement rejeté.');
    }
}
