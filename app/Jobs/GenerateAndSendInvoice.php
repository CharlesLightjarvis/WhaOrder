<?php

namespace App\Jobs;

use App\Actions\Orders\GenerateOrderInvoicePdf;
use App\Models\Order;
use App\Services\Waha\WahaClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAndSendInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $wahaSessionName,
        public readonly string $chatId,
    ) {}

    public function handle(GenerateOrderInvoicePdf $generateInvoice, WahaClient $client): void
    {
        $base64Pdf = $generateInvoice->handle($this->order);

        $client->sendFile(
            $this->wahaSessionName,
            $this->chatId,
            $base64Pdf,
            'application/pdf',
            "facture-{$this->order->id}.pdf",
        );
    }
}
