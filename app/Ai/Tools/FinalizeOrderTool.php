<?php

namespace App\Ai\Tools;

use App\Enums\ConversationStatus;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Conversation;
use App\Models\Delivery;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class FinalizeOrderTool implements Tool
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

        return DB::transaction(function () use ($draftOrder) {
            foreach ($draftOrder['items'] as $item) {
                $insufficient = $this->checkInsufficientStock($item);

                if ($insufficient) {
                    return $insufficient;
                }
            }

            $order = Order::query()->create([
                'customer_id' => $this->conversation->customer_id,
                'conversation_id' => $this->conversation->id,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'payment_method' => $draftOrder['payment_method'] ? PaymentMethod::from($draftOrder['payment_method']) : null,
                'delivery_address_text' => $draftOrder['delivery_address_text'],
                'delivery_city' => $draftOrder['delivery_city'],
                'subtotal' => $draftOrder['subtotal'],
                'delivery_fee' => $draftOrder['delivery_fee'],
                'total' => $draftOrder['total'],
            ]);

            foreach ($draftOrder['items'] as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'product_name_snapshot' => $item['product_name_snapshot'],
                    'variant_name_snapshot' => $item['variant_name_snapshot'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                ]);

                if ($item['variant_id']) {
                    ProductVariant::query()
                        ->where('id', $item['variant_id'])
                        ->whereHas('product', fn ($query) => $query->where('merchant_id', $this->merchant->id))
                        ->decrement('stock', $item['quantity']);
                } else {
                    Product::query()
                        ->where('id', $item['product_id'])
                        ->where('merchant_id', $this->merchant->id)
                        ->decrement('stock', $item['quantity']);
                }
            }

            Delivery::query()->create([
                'order_id' => $order->id,
                'status' => DeliveryStatus::Pending,
                'address_text' => $draftOrder['delivery_address_text'],
                'city' => $draftOrder['delivery_city'],
            ]);

            $this->conversation->update([
                'status' => ConversationStatus::Completed,
                'draft_order' => null,
            ]);

            return "Commande #{$order->id} confirmée. Total : ".number_format($draftOrder['total'], 2)." {$this->merchant->currency->value}.";
        });
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

        if (empty($draftOrder['delivery_address_text'] ?? null) && empty($draftOrder['delivery_city'] ?? null)) {
            $missing[] = "l'adresse de livraison";
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
