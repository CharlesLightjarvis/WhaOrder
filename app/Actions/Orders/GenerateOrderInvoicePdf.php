<?php

namespace App\Actions\Orders;

use App\Models\Order;
use Spatie\LaravelPdf\Facades\Pdf;

class GenerateOrderInvoicePdf
{
    /**
     * Render the order invoice to a base64-encoded PDF, ready to be sent
     * as a WhatsApp file attachment. Nothing is persisted to disk here —
     * the invoice is only ever generated on demand and sent directly to
     * the customer (see GenerateAndSendInvoice job). Saving invoices to a
     * disk for later retrieval is not implemented yet.
     */
    public function handle(Order $order): string
    {
        $order->loadMissing(['items', 'customer', 'merchant']);

        $merchant = $order->merchant;

        return Pdf::view('pdf.invoice', ['order' => $order, 'merchant' => $merchant])
            ->headerView('pdf.invoice-header', ['order' => $order, 'merchant' => $merchant])
            ->footerView('pdf.invoice-footer', ['order' => $order, 'merchant' => $merchant])
            ->margins(top: 30, right: 15, bottom: 20, left: 15, unit: 'mm')
            ->base64();
    }
}
