<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Products\ProductRepository;
use Illuminate\Support\Facades\DB;

class UpdateProduct
{
    public function __construct(
        private readonly ProductRepository $repository,
        private readonly StoreUploadedImages $storeUploadedImages,
        private readonly PruneProductImages $pruneProductImages,
        private readonly SyncProductStockFromVariants $syncProductStock,
        private readonly CheckLowStock $checkLowStock,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $images = $data['images'] ?? [];
            $keepImageIds = $data['keep_image_ids'] ?? [];
            $variantsData = $data['variants'] ?? [];
            unset($data['images'], $data['keep_image_ids'], $data['variants']);

            $product = $this->repository->update($product, $data);

            $this->pruneProductImages->handle($product->images(), $keepImageIds);
            $this->storeUploadedImages->handle($product, $images);

            $keptVariantIds = [];

            foreach ($variantsData as $variantData) {
                $variantImages = $variantData['images'] ?? [];
                $variantKeepImageIds = $variantData['keep_image_ids'] ?? [];
                unset($variantData['images'], $variantData['keep_image_ids']);

                $variant = isset($variantData['id'])
                    ? $product->variants()->findOrFail((string) $variantData['id'])
                    : new ProductVariant;

                $variant->fill([
                    'name' => $variantData['name'],
                    'price' => $variantData['price'] ?? null,
                    'stock' => $variantData['stock'] ?? 0,
                ]);

                $product->variants()->save($variant);
                $keptVariantIds[] = $variant->id;

                $this->pruneProductImages->handle($variant->images(), $variantKeepImageIds);
                $this->storeUploadedImages->handle($product, $variantImages, $variant);
                $this->checkLowStock->handleVariant($variant);
            }

            $product->variants()->whereNotIn('id', $keptVariantIds)->delete();

            $this->syncProductStock->handle($product);
            $this->checkLowStock->handle($product);

            return $product->fresh(['category', 'images', 'variants.images']);
        });
    }
}
