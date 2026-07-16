<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Repositories\Products\ProductRepository;
use Illuminate\Support\Facades\DB;

class DeleteProduct
{
    public function __construct(
        private readonly ProductRepository $repository,
    ) {}

    public function handle(Product $product): void
    {
        DB::transaction(fn () => $this->repository->delete($product));
    }
}
