<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Repositories\Products\ProductRepository;
use Illuminate\Support\Facades\DB;

class CreateProduct
{
    public function __construct(
        private readonly ProductRepository $repository,
        private readonly StoreUploadedImages $storeUploadedImages,
        private readonly SyncProductStockFromVariants $syncProductStock,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $images = $data['images'] ?? [];
            $variantsData = $data['variants'] ?? [];
            unset($data['images'], $data['variants']);

            $product = $this->repository->create($data);

            $this->storeUploadedImages->handle($product, $images);

            foreach ($variantsData as $variantData) {
                $variantImages = $variantData['images'] ?? [];
                unset($variantData['images']);

                $variant = $product->variants()->create([
                    'name' => $variantData['name'],
                    'price' => $variantData['price'] ?? null,
                    'stock' => $variantData['stock'] ?? 0,
                ]);

                $this->storeUploadedImages->handle($product, $variantImages, $variant);
            }

            $this->syncProductStock->handle($product);

            return $product->fresh(['category', 'images', 'variants.images']);
        });
    }
}
