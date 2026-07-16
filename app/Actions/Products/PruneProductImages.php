<?php

namespace App\Actions\Products;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PruneProductImages
{
    /**
     * @param  array<int, int>  $keepIds
     */
    public function handle(HasMany $images, array $keepIds): void
    {
        DB::transaction(function () use ($images, $keepIds): void {
            $images->whereNotIn('id', $keepIds)->get()->each(function (ProductImage $image): void {
                $publicPrefix = Storage::disk('public')->url('');

                if (str_starts_with($image->url, $publicPrefix)) {
                    Storage::disk('public')->delete(Str::after($image->url, $publicPrefix));
                }

                $image->delete();
            });
        });
    }
}
