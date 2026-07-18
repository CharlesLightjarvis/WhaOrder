<?php

namespace App\Ai\Tools;

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchProductTool implements Tool
{
    /**
     * Below this similarity score (0-100, from similar_text), a fuzzy match
     * is considered unrelated noise rather than a plausible typo.
     */
    private const SIMILARITY_THRESHOLD = 40.0;

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
        return "Recherche ou liste les produits actifs du catalogue, numérotés. Accepte un nom approximatif (tolère les fautes de frappe du client) et/ou une catégorie. Laisse le nom vide pour lister tous les produits d'une catégorie.";
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $term = $request->string('nom')->toString() ?: null;
        $categoryTerm = $request->string('categorie')->toString() ?: null;

        $query = Product::query()
            ->where('merchant_id', $this->merchant->id)
            ->where('is_active', true)
            ->with('variants');

        if ($categoryTerm) {
            $categoryId = $this->resolveCategoryId($categoryTerm);

            if (! $categoryId) {
                return "Aucune catégorie ne correspond à « {$categoryTerm} ». Utilise l'outil de liste des catégories pour voir les catégories disponibles.";
            }

            $query->where('category_id', $categoryId);
        }

        $products = $term
            ? $this->searchByName($query, $term)
            : $query->limit(20)->get();

        if ($products->isEmpty()) {
            return $term
                ? "Aucun produit ne correspond à « {$term} »."
                : 'Aucun produit actif dans cette catégorie.';
        }

        return $this->numberedList($products);
    }

    private function searchByName(Builder $query, string $term): Collection
    {
        $exact = (clone $query)->where('name', 'like', "%{$term}%")->limit(5)->get();

        if ($exact->isNotEmpty()) {
            return $exact;
        }

        return (clone $query)->get()
            ->map(fn (Product $product) => [$product, $this->similarity($term, $product->name)])
            ->filter(fn (array $pair) => $pair[1] >= self::SIMILARITY_THRESHOLD)
            ->sortByDesc(fn (array $pair) => $pair[1])
            ->take(5)
            ->map(fn (array $pair) => $pair[0])
            ->values();
    }

    private function resolveCategoryId(string $term): ?string
    {
        $categories = Category::query()->where('merchant_id', $this->merchant->id)->get();

        $exact = $categories->first(
            fn (Category $category) => str_contains(mb_strtolower($category->name), mb_strtolower($term))
        );

        if ($exact) {
            return $exact->id;
        }

        $best = $categories
            ->map(fn (Category $category) => [$category, $this->similarity($term, $category->name)])
            ->sortByDesc(fn (array $pair) => $pair[1])
            ->first();

        return $best && $best[1] >= self::SIMILARITY_THRESHOLD ? $best[0]->id : null;
    }

    private function similarity(string $a, string $b): float
    {
        similar_text(mb_strtolower($a), mb_strtolower($b), $percent);

        return $percent;
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    private function numberedList(Collection $products): string
    {
        $currency = $this->merchant->currency->value;
        $lines = [];
        $number = 1;

        foreach ($products as $product) {
            if ($product->variants->isEmpty()) {
                $lines[] = sprintf(
                    '%d. %s — %s %s — stock=%d — id=%s',
                    $number++,
                    $product->name,
                    number_format((float) $product->price, 2),
                    $currency,
                    $product->stock,
                    $product->id,
                );

                continue;
            }

            foreach ($product->variants as $variant) {
                $lines[] = sprintf(
                    '%d. %s (%s) — %s %s — stock=%d — id=%s/%s',
                    $number++,
                    $product->name,
                    $variant->name,
                    number_format((float) ($variant->price ?? $product->price), 2),
                    $currency,
                    $variant->stock,
                    $product->id,
                    $variant->id,
                );
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'nom' => $schema->string()
                ->description('Le nom, ou une partie du nom, du produit recherché. Peut contenir des fautes de frappe. Laisse vide pour lister tous les produits de la catégorie donnée.')
                ->nullable(),
            'categorie' => $schema->string()
                ->description('Le nom de la catégorie choisie par le client, si connu. Peut contenir des fautes de frappe.')
                ->nullable(),
        ];
    }
}
