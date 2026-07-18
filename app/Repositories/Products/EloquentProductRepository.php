<?php

namespace App\Repositories\Products;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository implements ProductRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with([
                'category:id,name',
                'images' => fn ($query) => $query->whereNull('variant_id')->ordered()->limit(1),
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
