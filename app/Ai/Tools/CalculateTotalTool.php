<?php

namespace App\Ai\Tools;

use App\Enums\PaymentMethod;
use App\Models\Conversation;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CalculateTotalTool implements Tool
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
        return 'calculate_total';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return "Recalcule le panier et son total à partir de la liste COMPLÈTE des articles actuellement discutés avec le client (pas seulement les nouveaux). Met aussi à jour l'adresse de livraison et le moyen de paiement si le client les a donnés. Retourne un récapitulatif à présenter au client.";
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $items = $request->array('items');
        $address = $request->string('adresse_livraison')->toString() ?: null;
        $city = $request->string('ville_livraison')->toString() ?: null;
        $rawPaymentMethod = $request->string('methode_paiement')->toString() ?: null;

        if (empty($items)) {
            return "La liste d'articles est vide.";
        }

        $resolvedItems = [];
        $subtotal = 0.0;

        foreach ($items as $item) {
            $product = Product::query()
                ->where('merchant_id', $this->merchant->id)
                ->with('variants')
                ->find($item['product_id'] ?? null);

            if (! $product || ! $product->is_active) {
                return 'Produit introuvable ou inactif (product_id='.($item['product_id'] ?? '?').').';
            }

            $variant = null;

            if (! empty($item['variant_id'])) {
                $variant = $product->variants->firstWhere('id', $item['variant_id']);

                if (! $variant) {
                    return "Variante introuvable pour {$product->name}.";
                }
            }

            $quantity = (int) ($item['quantite'] ?? 0);

            if ($quantity < 1) {
                return "Quantité invalide pour {$product->name}.";
            }

            $stock = $variant?->stock ?? $product->stock;
            $unitPrice = (float) ($variant?->price ?? $product->price);

            if ($stock < $quantity) {
                $label = $variant ? "{$product->name} ({$variant->name})" : $product->name;

                return "Stock insuffisant pour {$label} : il ne reste que {$stock} unité(s).";
            }

            $lineTotal = $unitPrice * $quantity;
            $subtotal += $lineTotal;

            $resolvedItems[] = [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'product_name_snapshot' => $product->name,
                'variant_name_snapshot' => $variant?->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        $previousDraft = $this->conversation->draft_order ?? [];
        $deliveryFee = 0.0;

        $draftOrder = [
            'items' => $resolvedItems,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $subtotal + $deliveryFee,
            'delivery_address_text' => $address ?? ($previousDraft['delivery_address_text'] ?? null),
            'delivery_city' => $city ?? ($previousDraft['delivery_city'] ?? null),
            'payment_method' => $this->normalizePaymentMethod($rawPaymentMethod) ?? ($previousDraft['payment_method'] ?? null),
        ];

        DB::transaction(fn () => $this->conversation->update(['draft_order' => $draftOrder]));

        return $this->summarize($draftOrder);
    }

    private function normalizePaymentMethod(?string $raw): ?string
    {
        if (! $raw) {
            return null;
        }

        $raw = mb_strtolower($raw);

        return match (true) {
            str_contains($raw, 'mobile') || str_contains($raw, 'momo') || str_contains($raw, 'orange money') || str_contains($raw, 'wave') => PaymentMethod::MobileMoney->value,
            str_contains($raw, 'espèce') || str_contains($raw, 'espece') || str_contains($raw, 'cash') || str_contains($raw, 'livraison') => PaymentMethod::Cash->value,
            str_contains($raw, 'carte') || str_contains($raw, 'card') => PaymentMethod::Card->value,
            default => PaymentMethod::Other->value,
        };
    }

    /**
     * @param  array<string, mixed>  $draftOrder
     */
    private function summarize(array $draftOrder): string
    {
        $currency = $this->merchant->currency->value;

        $lines = collect($draftOrder['items'])->map(fn (array $item) => sprintf(
            '- %s%s x%d = %s %s',
            $item['product_name_snapshot'],
            $item['variant_name_snapshot'] ? " ({$item['variant_name_snapshot']})" : '',
            $item['quantity'],
            number_format($item['line_total'], 2),
            $currency,
        ))->implode("\n");

        $summary = "{$lines}\nSous-total : ".number_format($draftOrder['subtotal'], 2)." {$currency}"
            ."\nLivraison : ".number_format($draftOrder['delivery_fee'], 2)." {$currency}"
            ."\nTotal : ".number_format($draftOrder['total'], 2)." {$currency}";

        $summary .= ($draftOrder['delivery_address_text'] || $draftOrder['delivery_city'])
            ? "\nAdresse : ".trim(($draftOrder['delivery_address_text'] ?? '').' '.($draftOrder['delivery_city'] ?? ''))
            : "\nAdresse de livraison manquante.";

        $summary .= $draftOrder['payment_method']
            ? "\nMoyen de paiement : {$draftOrder['payment_method']}"
            : "\nMoyen de paiement manquant.";

        return $summary;
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'items' => $schema->array()
                ->items($schema->object([
                    'product_id' => $schema->string()->required(),
                    'variant_id' => $schema->string()->nullable(),
                    'quantite' => $schema->integer()->required(),
                ]))
                ->description('La liste COMPLÈTE des articles actuellement dans le panier du client (pas seulement les nouveaux).')
                ->required(),
            'adresse_livraison' => $schema->string()
                ->description('Adresse de livraison donnée par le client, si connue.')
                ->nullable(),
            'ville_livraison' => $schema->string()
                ->description('Ville de livraison, si connue.')
                ->nullable(),
            'methode_paiement' => $schema->string()
                ->description('Moyen de paiement choisi par le client (mobile money, espèces, carte), si connu.')
                ->nullable(),
        ];
    }
}
