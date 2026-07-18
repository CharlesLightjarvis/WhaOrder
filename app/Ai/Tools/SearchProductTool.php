<?php

namespace App\Ai\Tools;

use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchProductTool implements Tool
{
    public function __construct(
        private readonly Merchant $merchant,
    ) {}

    /**
     * Get the name the AI model uses to call this tool.
     */
    public function name(): string
    {
        return 'search_product';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Recherche des produits actifs du catalogue par nom. Retourne leurs identifiants, prix et stock. À utiliser dès que le client mentionne un article.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $term = $request->string('nom')->toString();

        $products = Product::query()
            ->where('merchant_id', $this->merchant->id)
            ->where('is_active', true)
            ->where('name', 'like', "%{$term}%")
            ->with('variants')
            ->limit(5)
            ->get();

        if ($products->isEmpty()) {
            return "Aucun produit actif ne correspond à « {$term} ».";
        }

        return $products->map(fn (Product $product) => $this->describeProduct($product))->implode("\n");
    }

    private function describeProduct(Product $product): string
    {
        $currency = $this->merchant->currency->value;

        if ($product->variants->isEmpty()) {
            return sprintf(
                '- %s | product_id=%s | prix=%s %s | stock=%d',
                $product->name,
                $product->id,
                number_format((float) $product->price, 2),
                $currency,
                $product->stock,
            );
        }

        return $product->variants->map(fn ($variant) => sprintf(
            '- %s (%s) | product_id=%s | variant_id=%s | prix=%s %s | stock=%d',
            $product->name,
            $variant->name,
            $product->id,
            $variant->id,
            number_format((float) ($variant->price ?? $product->price), 2),
            $currency,
            $variant->stock,
        ))->implode("\n");
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'nom' => $schema->string()
                ->description('Le nom, ou une partie du nom, du produit recherché.')
                ->required(),
        ];
    }
}
