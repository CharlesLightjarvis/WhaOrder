<?php

use App\Actions\Products\StoreUploadedImages;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('imagekit');
    config(['filesystems.disks.imagekit.endpoint_url' => 'https://ik.example.test']);

    $this->merchant = Merchant::factory()->create();
    app()->instance('currentMerchantId', $this->merchant->id);
    $this->product = Product::factory()->for($this->merchant)->create();
});

it('uses collision-resistant names for uploaded product images', function () {
    app(StoreUploadedImages::class)->handle($this->product, [
        UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg'),
        UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg'),
    ]);

    $paths = Storage::disk('imagekit')->allFiles();

    expect($paths)->toHaveCount(2)
        ->and($paths[0])->not->toBe($paths[1]);
});

it('removes uploaded files when the database write fails', function () {
    ProductImage::creating(function (): void {
        throw new RuntimeException('Database write failed.');
    });

    expect(fn () => app(StoreUploadedImages::class)->handle($this->product, [
        UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg'),
    ]))->toThrow(RuntimeException::class);

    expect(Storage::disk('imagekit')->allFiles())->toBeEmpty();
});
