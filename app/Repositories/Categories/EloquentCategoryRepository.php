<?php

namespace App\Repositories\Categories;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentCategoryRepository implements CategoryRepository
{
    /** @return LengthAwarePaginator<int, Category> */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(string $id): Category
    {
        return Category::query()->findOrFail($id);
    }

    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
