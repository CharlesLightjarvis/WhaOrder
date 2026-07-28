<?php

namespace App\Ai\Tools;

use App\Models\Conversation;
use App\Models\Merchant;
use App\Models\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCustomerOrderStatusTool implements Tool
{
    public function __construct(
        private readonly Merchant $merchant,
        private readonly Conversation $conversation,
    ) {}

    /**
     * Get the name the AI model uses to call this tool.
     */
    public function name(): string
    {
        return 'get_customer_order_status';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return "Récupère les commandes récentes de ce client avec leur statut de paiement, de livraison, et le détail de leurs articles. Le statut de livraison est la seule information de suivi à donner au client (le statut interne de la commande n'a pas à lui être communiqué, ils sont toujours alignés). Utilise la référence de commande et les order_item_id renvoyés ici pour appeler modify_order si le client veut changer sa commande. À utiliser dès que le client demande où en est sa commande, sa livraison, ou se plaint d'un retard : ne réponds jamais sur le statut d'une commande sans passer par cet outil.";
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $orders = Order::query()
            ->where('merchant_id', $this->merchant->id)
            ->where('customer_id', $this->conversation->customer_id)
            ->with(['delivery', 'items'])
            ->latest()
            ->limit(3)
            ->get();

        if ($orders->isEmpty()) {
            return "Ce client n'a aucune commande enregistrée.";
        }

        return $orders
            ->map(function (Order $order) {
                $reference = mb_strtoupper(substr($order->id, 0, 8));
                $delivery = $order->delivery;

                $lines = [
                    "Commande #{$reference} du {$order->created_at->translatedFormat('d/m/Y')} — {$order->total} {$this->merchant->currency->value}",
                    "Statut paiement : {$order->payment_status->label()}",
                ];

                $lines[] = $delivery
                    ? "Statut livraison : {$delivery->status->label()}".($delivery->city ? " ({$delivery->city})" : '')
                    : 'Statut livraison : non renseigné.';

                $lines[] = 'Articles :';
                $lines = [...$lines, ...$order->items->map(fn ($item) => sprintf(
                    '- [order_item_id=%s] %s%s — %d x %s = %s',
                    $item->id,
                    $item->product_name_snapshot,
                    $item->variant_name_snapshot ? " ({$item->variant_name_snapshot})" : '',
                    $item->quantity,
                    $item->unit_price,
                    $item->line_total,
                ))->all()];

                return implode("\n", $lines);
            })
            ->implode("\n\n");
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
