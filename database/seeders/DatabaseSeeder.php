<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Model events are relied upon here (slug generation, image ordering,
     * merchant auto-assignment), so this seeder does not disable them.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Merchant::factory()
            ->count(3)
            ->create()
            ->each(function (Merchant $merchant): void {
                User::factory()->create([
                    'merchant_id' => $merchant->id,
                    'name' => fake()->name(),
                    'email' => 'owner+'.$merchant->id.'@whaorder.test',
                ]);

                $categories = Category::factory()
                    ->count(4)
                    ->for($merchant)
                    ->create();

                Product::factory()
                    ->count(15)
                    ->for($merchant)
                    ->recycle($categories)
                    ->create()
                    ->each(function (Product $product): void {
                        ProductImage::factory()
                            ->count(2)
                            ->for($product)
                            ->create();

                        if (fake()->boolean(60)) {
                            ProductVariant::factory()
                                ->count(fake()->numberBetween(2, 4))
                                ->for($product)
                                ->create();
                        }
                    });

                Customer::factory()
                    ->count(20)
                    ->for($merchant)
                    ->create()
                    ->each(function (Customer $customer): void {
                        Address::factory()
                            ->for($customer)
                            ->create();
                    });
            });
    }
}
