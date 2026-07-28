<?php

namespace App\Ai\Tools;

use App\Actions\Orders\ModifyOrder;
use App\Actions\Products\NormalizeVariantId;
use App\Enums\OrderModificationAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Jobs\NotifyMerchantOfOrderModification;
use App\Models\Conversation;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ModifyOrderTool implements Tool
{
    public function __construct(
        private readonly Merchant $merchant,
        private readonly Conversation $conversation,
        private readonly ModifyOrder $modifyOrder,
        private readonly NormalizeVariantId $normalizeVariantId,
    ) {}

    /**
     * Get the name the AI model uses to call this tool.
     */
    public function name(): string
    {
        return 'modify_order';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return "Modifie une commande déjà confirmée : ajouter un article, en retirer un, changer une quantité, changer l'adresse de livraison, ou changer le moyen de paiement. Uniquement possible tant que la commande n'est pas encore en livraison. Récupère d'abord la référence de commande et les order_item_id via get_customer_order_status. Ne l'utilise qu'après confirmation explicite du client sur le changement précis à effectuer.";
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $action = OrderModificationAction::tryFrom($request->string('action')->toString());

        if (! $action) {
            return 'Action de modification invalide.';
        }

        $order = $this->resolveOrder($request->string('order_reference')->toString());

        if (! $order) {
            return 'Commande introuvable pour cette référence.';
        }

        if (! in_array($order->status, [OrderStatus::Pending, OrderStatus::Preparing], true)) {
            return 'Cette commande ne peut plus être modifiée, elle est déjà en cours de livraison ou terminée. Le client doit contacter le commerçant directement.';
        }

        $result = match ($action) {
            OrderModificationAction::AddItem => $this->addItem($request, $order),
            OrderModificationAction::RemoveItem => $this->removeItem($request, $order),
            OrderModificationAction::ChangeQuantity => $this->changeQuantity($request, $order),
            OrderModificationAction::ChangeDelivery => $this->changeDelivery($request, $order),
            OrderModificationAction::ChangePaymentMethod => $this->changePaymentMethod($request, $order),
        };

        if ($result['order'] instanceof Order) {
            NotifyMerchantOfOrderModification::dispatch($result['order'], $result['summary']);
        }

        return $result['message'];
    }

    /**
     * @return array{message: string, order: ?Order, summary: ?string}
     */
    private function addItem(Request $request, Order $order): array
    {
        $productId = $request->string('product_id')->toString();
        $variantId = $this->normalizeVariantId->handle($request->string('variant_id')->toString());
        $quantity = $request->integer('quantity');

        if (! $productId || $quantity < 1) {
            return $this->failure('Produit ou quantité manquant pour ajouter un article.');
        }

        $product = Product::query()
            ->where('merchant_id', $this->merchant->id)
            ->with('variants')
            ->find($productId);

        if (! $product || ! $product->is_active) {
            return $this->failure('Produit introuvable ou inactif.');
        }

        $variant = null;

        if ($variantId) {
            $variant = $product->variants->firstWhere('id', $variantId);

            if (! $variant) {
                return $this->failure('Variante introuvable pour ce produit.');
            }
        }

        $stock = $variant?->stock ?? $product->stock;

        if ($stock < $quantity) {
            return $this->failure("Stock insuffisant : il ne reste que {$stock} unité(s).");
        }

        $updated = $this->modifyOrder->addItem($order, $product, $variant, $quantity);
        $label = $product->name.($variant ? " ({$variant->name})" : '');

        return $this->success(
            $updated,
            "Article ajouté : *{$label}* x{$quantity}. Nouveau total : *{$updated->total} {$this->merchant->currency->value}*.",
            "ajout de {$label} x{$quantity}",
        );
    }

    /**
     * @return array{message: string, order: ?Order, summary: ?string}
     */
    private function removeItem(Request $request, Order $order): array
    {
        $item = $this->resolveItem($request, $order);

        if (! $item) {
            return $this->failure('Article introuvable dans cette commande.');
        }

        if ($order->items->count() <= 1) {
            return $this->failure("Impossible de retirer le dernier article d'une commande : le client doit contacter le commerçant pour annuler entièrement la commande.");
        }

        $label = $item->product_name_snapshot.($item->variant_name_snapshot ? " ({$item->variant_name_snapshot})" : '');
        $updated = $this->modifyOrder->removeItem($order, $item);

        return $this->success(
            $updated,
            "Article retiré : *{$label}*. Nouveau total : *{$updated->total} {$this->merchant->currency->value}*.",
            "retrait de {$label}",
        );
    }

    /**
     * @return array{message: string, order: ?Order, summary: ?string}
     */
    private function changeQuantity(Request $request, Order $order): array
    {
        $item = $this->resolveItem($request, $order);
        $quantity = $request->integer('quantity');

        if (! $item) {
            return $this->failure('Article introuvable dans cette commande.');
        }

        if ($quantity < 1) {
            return $this->failure('Pour retirer complètement un article, utilise remove_item plutôt que change_quantity avec une quantité de zéro.');
        }

        $delta = $quantity - $item->quantity;
        $stock = $item->variant?->stock ?? $item->product?->stock ?? 0;

        if ($delta > 0 && $stock < $delta) {
            return $this->failure("Stock insuffisant pour augmenter la quantité : il ne reste que {$stock} unité(s) supplémentaire(s) disponible(s).");
        }

        $label = $item->product_name_snapshot.($item->variant_name_snapshot ? " ({$item->variant_name_snapshot})" : '');
        $updated = $this->modifyOrder->changeQuantity($order, $item, $quantity);

        return $this->success(
            $updated,
            "Quantité mise à jour : *{$label}* x{$quantity}. Nouveau total : *{$updated->total} {$this->merchant->currency->value}*.",
            "quantité de {$label} changée à {$quantity}",
        );
    }

    /**
     * @return array{message: string, order: ?Order, summary: ?string}
     */
    private function changeDelivery(Request $request, Order $order): array
    {
        $city = $request->string('delivery_city')->toString() ?: null;
        $address = $request->string('delivery_address_text')->toString() ?: null;

        if (! $city && ! $address) {
            return $this->failure('Aucune nouvelle ville ni adresse fournie.');
        }

        $updated = $this->modifyOrder->changeDelivery($order, $city, $address);

        return $this->success(
            $updated,
            "Livraison mise à jour : *{$updated->delivery_city}*, *{$updated->delivery_address_text}*.",
            'adresse de livraison modifiée',
        );
    }

    /**
     * @return array{message: string, order: ?Order, summary: ?string}
     */
    private function changePaymentMethod(Request $request, Order $order): array
    {
        $method = PaymentMethod::tryFrom($request->string('payment_method')->toString());

        if (! $method) {
            return $this->failure('Moyen de paiement invalide.');
        }

        $updated = $this->modifyOrder->changePaymentMethod($order, $method);

        return $this->success(
            $updated,
            "Moyen de paiement mis à jour : *{$method->label()}*.",
            'moyen de paiement modifié',
        );
    }

    private function resolveOrder(string $reference): ?Order
    {
        if (! $reference) {
            return null;
        }

        return Order::query()
            ->where('merchant_id', $this->merchant->id)
            ->where('customer_id', $this->conversation->customer_id)
            ->where('id', 'like', mb_strtolower($reference).'%')
            ->with('items.product', 'items.variant', 'delivery')
            ->first();
    }

    private function resolveItem(Request $request, Order $order): ?OrderItem
    {
        $itemId = $request->string('order_item_id')->toString();

        if (! $itemId) {
            return null;
        }

        return $order->items->firstWhere('id', $itemId);
    }

    /**
     * @return array{message: string, order: ?Order, summary: ?string}
     */
    private function success(Order $order, string $message, string $summary): array
    {
        return ['message' => $message, 'order' => $order, 'summary' => $summary];
    }

    /**
     * @return array{message: string, order: ?Order, summary: ?string}
     */
    private function failure(string $message): array
    {
        return ['message' => $message, 'order' => null, 'summary' => null];
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'order_reference' => $schema->string()
                ->description('Référence de la commande à modifier, obtenue via get_customer_order_status (ex : 019F7B0B).')
                ->required(),
            'action' => $schema->string()
                ->enum(OrderModificationAction::class)
                ->description('Type de modification à effectuer.')
                ->required(),
            'order_item_id' => $schema->string()
                ->description("Identifiant de l'article concerné, obtenu via get_customer_order_status. Requis pour remove_item et change_quantity.")
                ->nullable(),
            'product_id' => $schema->string()
                ->description('Identifiant du produit à ajouter, obtenu via search_product. Requis pour add_item.')
                ->nullable(),
            'variant_id' => $schema->string()
                ->description('Identifiant de la variante à ajouter, si applicable.')
                ->nullable(),
            'quantity' => $schema->integer()
                ->description('Nouvelle quantité (change_quantity) ou quantité à ajouter (add_item).')
                ->nullable(),
            'delivery_city' => $schema->string()
                ->description('Nouvelle ville de livraison. Pour change_delivery.')
                ->nullable(),
            'delivery_address_text' => $schema->string()
                ->description('Nouvelle adresse détaillée. Pour change_delivery.')
                ->nullable(),
            'payment_method' => $schema->string()
                ->enum(PaymentMethod::class)
                ->description('Nouveau moyen de paiement. Pour change_payment_method.')
                ->nullable(),
        ];
    }
}
