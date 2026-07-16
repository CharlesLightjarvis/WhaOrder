<?php

namespace App\Actions\Categories;

use App\Models\Category;
use App\Repositories\Categories\CategoryRepository;
use Illuminate\Support\Facades\DB;

class DeleteCategory
{
    public function __construct(
        private readonly CategoryRepository $repository,
    ) {}

    public function handle(Category $category): void
    {
        DB::transaction(fn () => $this->repository->delete($category));
    }
}
