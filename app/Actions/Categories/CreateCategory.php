<?php

namespace App\Actions\Categories;

use App\Models\Category;
use App\Repositories\Categories\CategoryRepository;
use Illuminate\Support\Facades\DB;

class CreateCategory
{
    public function __construct(
        private readonly CategoryRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Category
    {
        return DB::transaction(fn () => $this->repository->create($data));
    }
}
