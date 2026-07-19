<?php

namespace App\Ai\Tools;

use App\Actions\Orders\FinalizeOrder;
use App\Jobs\GenerateAndSendInvoice;
use App\Jobs\NotifyMerchantOfNewOrder;
use App\Models\Conversation;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class FinalizeOrderTool implements Tool
{
    public function __construct(
        private readonly Merchant $merchant,
        private readonly Conversation $conversation,
        private readonly FinalizeOrder $finalizeOrder,
        private readonly string $sessionName,
        private readonly string $chatId,
    ) {}

    /**
     * Get the name the AI model uses to call this tool.
     */
    public function name(): string
    {
        return 'finalize_order';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return "Finalise la commande à partir du panier déjà calculé via calculate_total. N'appelle cet outil QUE lorsque les articles, l'adresse de livraison et le moyen de paiement sont connus, et que le client a confirmé vouloir passer commande.";
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $draftOrder = $this->conversation->draft_order;

        $missing = $this->missingFields($draftOrder);

        if (! empty($missing)) {
            return 'Impossible de finaliser, il manque : '.implode(', ', $missing).'.';
        }

        foreach ($draftOrder['items'] as $item) {
            $insufficient = $this->checkInsufficientStock($item);

            if ($insufficient) {
                return $insufficient;
            }
        }

        $order = $this->finalizeOrder->handle($this->merchant, $this->conversation, $draftOrder);

        NotifyMerchantOfNewOrder::dispatch($order, $this->sessionName);
        GenerateAndSendInvoice::dispatch($order, $this->sessionName, $this->chatId);

        return "Commande #{$order->id} confirmée. Total : ".number_format($draftOrder['total'], 2)." {$this->merchant->currency->value}.";
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function checkInsufficientStock(array $item): ?string
    {
        if ($item['variant_id']) {
            $stock = ProductVariant::query()
                ->where('id', $item['variant_id'])
                ->whereHas('product', fn ($query) => $query->where('merchant_id', $this->merchant->id))
                ->first()?->stock ?? 0;
        } else {
            $stock = Product::query()
                ->where('id', $item['product_id'])
                ->where('merchant_id', $this->merchant->id)
                ->first()?->stock ?? 0;
        }

        if ($stock < $item['quantity']) {
            return "Stock insuffisant pour {$item['product_name_snapshot']}, recalculez le panier.";
        }

        return null;
    }

    /**
     * @param  ?array<string, mixed>  $draftOrder
     * @return array<int, string>
     */
    private function missingFields(?array $draftOrder): array
    {
        $missing = [];

        if (empty($draftOrder['items'] ?? [])) {
            $missing[] = 'les articles';
        }

        if (empty($draftOrder['customer_name'] ?? null) && empty($this->conversation->customer?->name)) {
            $missing[] = 'le nom du client';
        }

        if (empty($draftOrder['delivery_city'] ?? null)) {
            $missing[] = 'la ville de livraison';
        }

        if (empty($draftOrder['delivery_address_text'] ?? null)) {
            $missing[] = "l'adresse de livraison détaillée";
        }

        if (empty($draftOrder['payment_method'] ?? null)) {
            $missing[] = 'le moyen de paiement';
        }

        return $missing;
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
