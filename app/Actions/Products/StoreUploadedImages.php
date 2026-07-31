<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class StoreUploadedImages
{
    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function handle(Product $product, array $files, ?ProductVariant $variant = null): void
    {
        $uploadedPaths = [];
        $folder = "whaorder/images/{$product->merchant->slug}/products";

        try {
            foreach ($files as $file) {
                $extension = mb_strtolower($file->getClientOriginalExtension());
                $path = $folder.'/'.Str::uuid().($extension !== '' ? ".{$extension}" : '');

                Storage::disk('imagekit')->put($path, $file->getContent());
                $uploadedPaths[] = $path;
            }

            DB::transaction(function () use ($product, $variant, $uploadedPaths): void {
                foreach ($uploadedPaths as $path) {
                    $product->images()->create([
                        'url' => rtrim((string) config('filesystems.disks.imagekit.endpoint_url'), '/').'/'.$path,
                        'variant_id' => $variant?->id,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($uploadedPaths as $uploadedPath) {
                Storage::disk('imagekit')->delete($uploadedPath);
            }

            throw $exception;
        }
    }
}
