<?php

namespace App\Ai\Tools;

use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetProductVariantsTool implements Tool
{
    public function __construct(
        private readonly Merchant $merchant,
    ) {}

    /**
     * Get the name the AI model uses to call this tool.
     */
    public function name(): string
    {
        return 'get_product_variants';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return "Liste les variantes d'un produit précis (taille, couleur...), numérotées avec prix et stock. À utiliser juste après que le client a choisi un produit qui en a plusieurs, pour lui faire choisir laquelle. Le résultat donne product_id et variant_id séparément : réutilise-les tels quels, chacun dans son propre champ, dans les outils suivants (ne les combine jamais en une seule valeur).";
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $productId = $request->string('product_id')->toString();

        $product = Product::query()
            ->where('merchant_id', $this->merchant->id)
            ->where('is_active', true)
            ->with('variants')
            ->find($productId);

        if (! $product) {
            return 'Produit introuvable.';
        }

        if ($product->variants->isEmpty()) {
            return "Ce produit n'a pas de variante, il peut être ajouté directement au panier.";
        }

        $currency = $this->merchant->currency;
        $lines = [];
        $number = 1;

        foreach ($product->variants as $variant) {
            $lines[] = sprintf(
                '%d. %s — %s %s — stock=%d — product_id=%s — variant_id=%s',
                $number++,
                $variant->name,
                number_format((float) ($variant->price ?? $product->price), 2),
                $currency,
                $variant->stock,
                $product->id,
                $variant->id,
            );
        }

        return implode("\n", $lines);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product_id' => $schema->string()
                ->description('Identifiant du produit dont on veut lister les variantes.')
                ->required(),
        ];
    }
}
