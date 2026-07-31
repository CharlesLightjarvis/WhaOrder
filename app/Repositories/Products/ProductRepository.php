<?php

namespace App\Repositories\Products;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepository
{
    /** @return LengthAwarePaginator<int, Product> */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, Product>
     */
    public function all(): Collection;

    public function find(string $id): Product;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product;

    public function delete(Product $product): void;
}
