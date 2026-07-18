<?php

namespace App\Ai\Tools;

use App\Models\Category;
use App\Models\Merchant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListCategoriesTool implements Tool
{
    public function __construct(
        private readonly Merchant $merchant,
    ) {}

    /**
     * Get the name the AI model uses to call this tool.
     */
    public function name(): string
    {
        return 'list_categories';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return "Liste les catégories de produits du commerçant, numérotées. À utiliser en début de conversation, ou dès que le client veut voir le catalogue sans préciser de produit exact.";
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $categories = Category::query()
            ->where('merchant_id', $this->merchant->id)
            ->orderBy('name')
            ->get();

        if ($categories->isEmpty()) {
            return "Ce commerçant n'a aucune catégorie de produits configurée.";
        }

        return $categories
            ->values()
            ->map(fn (Category $category, int $index) => ($index + 1).'. '.$category->name)
            ->implode("\n");
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
