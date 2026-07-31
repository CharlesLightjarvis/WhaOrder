<?php

namespace App\Ai\Tools;

use App\Actions\Products\NormalizeVariantId;
use App\Models\Merchant;
use App\Models\Product;
use App\Services\Waha\WahaClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Http\Client\RequestException;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SendProductPhotosTool implements Tool
{
    /**
     * Cap the number of images sent per call to avoid flooding the chat.
     */
    private const MAX_PHOTOS = 3;

    public function __construct(
        private readonly Merchant $merchant,
        private readonly WahaClient $client,
        private readonly string $sessionName,
        private readonly string $chatId,
        private readonly NormalizeVariantId $normalizeVariantId,
    ) {}

    /**
     * Get the name the AI model uses to call this tool.
     */
    public function name(): string
    {
        return 'send_product_photos';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return "Envoie au client les photos du produit (ou de la variante choisie si elle a ses propres photos). Utilise cet outil une seule fois, juste après que le client a confirmé le produit et sa variante s'il y en a une, avant de continuer la conversation.";
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $productId = $request->string('product_id')->toString();
        $variantId = $this->normalizeVariantId->handle($request->string('variant_id')->toString());

        $product = Product::query()
            ->where('merchant_id', $this->merchant->id)
            ->with(['images', 'variants.images'])
            ->find($productId);

        if (! $product) {
            return 'Produit introuvable, impossible d\'envoyer des photos.';
        }

        $images = collect();

        if ($variantId) {
            $variant = $product->variants->firstWhere('id', $variantId);
            $images = $variant ? $variant->images : collect();
        }

        if ($images->isEmpty()) {
            $images = $product->images->whereNull('variant_id')->values();
        }

        if ($images->isEmpty()) {
            return 'Aucune photo disponible pour ce produit.';
        }

        $sent = 0;

        foreach ($images->take(self::MAX_PHOTOS) as $image) {
            try {
                $this->client->sendImage($this->sessionName, $this->chatId, $image->url, $product->name);
                $sent++;
            } catch (RequestException) {
                // Skip a failed image, still report how many succeeded.
            }
        }

        return $sent > 0
            ? "{$sent} photo(s) envoyée(s) au client."
            : "Échec de l'envoi des photos.";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product_id' => $schema->string()
                ->description('Identifiant du produit dont il faut envoyer les photos.')
                ->required(),
            'variant_id' => $schema->string()
                ->description('Identifiant de la variante choisie, si elle a ses propres photos.')
                ->nullable(),
        ];
    }
}
