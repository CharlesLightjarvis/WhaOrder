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
     * @param  array<int, string>  $keepIds
     */
    public function handle(HasMany $images, array $keepIds): void
    {
        DB::transaction(function () use ($images, $keepIds): void {
            $images->whereNotIn('id', $keepIds)->get()->each(function (ProductImage $image): void {
                $imagekitPrefix = rtrim((string) config('filesystems.disks.imagekit.endpoint_url'), '/').'/';

                if (str_starts_with($image->url, $imagekitPrefix)) {
                    Storage::disk('imagekit')->delete(Str::after($image->url, $imagekitPrefix));
                }

                $publicPrefix = Storage::disk('public')->url('');

                if (str_starts_with($image->url, $publicPrefix)) {
                    Storage::disk('public')->delete(Str::after($image->url, $publicPrefix));
                }

                $image->delete();
            });
        });
    }
}
