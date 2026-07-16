<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Repositories\Products\ProductRepository;
use Illuminate\Support\Facades\DB;

class ToggleProductActive
{
    public function __construct(
        private readonly ProductRepository $repository,
    ) {}

    public function handle(Product $product): Product
    {
        return DB::transaction(fn () => $this->repository->update($product, [
            'is_active' => ! $product->is_active,
        ]));
    }
}
