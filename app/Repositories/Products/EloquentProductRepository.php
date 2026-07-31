<?php

namespace App\Repositories\Products;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepository
{
    /** @return LengthAwarePaginator<int, Product> */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with([
                'category:id,name',
                'images' => fn ($query) => $query->whereNull('variant_id')->ordered()->limit(1),
                'variants' => fn ($query) => $query->select(['id', 'product_id', 'name', 'price', 'stock']),
                'variants.images' => fn ($query) => $query->ordered()->limit(1),
            ])
            ->withCount('variants')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function all(): Collection
    {
        return Product::query()
            ->with([
                'category:id,name',
                'images' => fn ($query) => $query->whereNull('variant_id')->ordered()->limit(1),
                'variants' => fn ($query) => $query->select(['id', 'product_id', 'name', 'price', 'stock']),
                'variants.images' => fn ($query) => $query->ordered()->limit(1),
            ])
            ->withCount('variants')
            ->latest()
            ->get();
    }

    public function find(string $id): Product
    {
        return Product::query()
            ->with([
                'category:id,name',
                'images' => fn ($query) => $query->ordered(),
                'variants.images' => fn ($query) => $query->ordered(),
            ])
            ->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
