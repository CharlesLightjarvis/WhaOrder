<?php

namespace App\Ai\Tools;

use App\Actions\Products\NormalizeVariantId;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CheckStockTool implements Tool
{
    public function __construct(
        private readonly Merchant $merchant,
        private readonly NormalizeVariantId $normalizeVariantId,
    ) {}

    /**
     * Get the name the AI model uses to call this tool.
     */
    public function name(): string
    {
        return 'check_stock';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return "Vérifie si la quantité demandée d'un produit (ou d'une variante) est disponible en stock.";
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $productId = $request->string('product_id')->toString();
        $variantId = $this->normalizeVariantId->handle($request->string('variant_id')->toString());
        $quantity = $request->integer('quantite');

        $product = Product::query()
            ->where('merchant_id', $this->merchant->id)
            ->with('variants')
            ->find($productId);

        if (! $product || ! $product->is_active) {
            return 'Produit introuvable ou inactif.';
        }

        $stock = $product->stock;
        $label = $product->name;

        if ($variantId) {
            $variant = $product->variants->firstWhere('id', $variantId);

            if (! $variant) {
                return 'Variante introuvable pour ce produit.';
            }

            $stock = $variant->stock;
            $label = "{$product->name} ({$variant->name})";
        }

        if ($stock >= $quantity) {
            return "Disponible : {$label} — {$stock} en stock, quantité demandée {$quantity}.";
        }

        return "Stock insuffisant pour {$label} : il ne reste que {$stock} unité(s), quantité demandée {$quantity}.";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product_id' => $schema->string()
                ->description('Identifiant du produit, obtenu via chercher_produit.')
                ->required(),
            'variant_id' => $schema->string()
                ->description('Identifiant de la variante, si le produit en a une.')
                ->nullable(),
            'quantite' => $schema->integer()
                ->description('Quantité souhaitée par le client.')
                ->required(),
        ];
    }
}
