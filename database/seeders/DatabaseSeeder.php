<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentProofStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\PaymentProof;
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

                $products = Product::factory()
                    ->count(30)
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

                $customers = Customer::factory()
                    ->count(20)
                    ->for($merchant)
                    ->create()
                    ->each(function (Customer $customer): void {
                        Address::factory()
                            ->for($customer)
                            ->create();
                    });

                Conversation::factory()
                    ->count(8)
                    ->for($merchant)
                    ->recycle($customers)
                    ->create();

                Order::factory()
                    ->count(15)
                    ->for($merchant)
                    ->recycle($customers)
                    ->create()
                    ->each(function (Order $order) use ($products): void {
                        $items = $products->random(fake()->numberBetween(1, 3));

                        $subtotal = 0;

                        foreach ($items as $product) {
                            $quantity = fake()->numberBetween(1, 3);
                            $unitPrice = (float) $product->price;
                            $lineTotal = $quantity * $unitPrice;
                            $subtotal += $lineTotal;

                            $order->items()->create([
                                'product_id' => $product->id,
                                'product_variant_id' => null,
                                'product_name_snapshot' => $product->name,
                                'variant_name_snapshot' => null,
                                'quantity' => $quantity,
                                'unit_price' => $unitPrice,
                                'line_total' => $lineTotal,
                            ]);
                        }

                        $order->update([
                            'subtotal' => $subtotal,
                            'total' => $subtotal + $order->delivery_fee,
                        ]);

                        if ($order->payment_status !== PaymentStatus::Unpaid) {
                            PaymentProof::factory()
                                ->for($order)
                                ->create([
                                    'status' => $order->payment_status === PaymentStatus::Confirmed
                                        ? PaymentProofStatus::Confirmed
                                        : PaymentProofStatus::PendingReview,
                                ]);
                        }

                        if (in_array($order->status, [OrderStatus::Preparing, OrderStatus::OutForDelivery, OrderStatus::Delivered], true)) {
                            Delivery::factory()
                                ->for($order)
                                ->create([
                                    'status' => match ($order->status) {
                                        OrderStatus::Delivered => DeliveryStatus::Delivered,
                                        OrderStatus::OutForDelivery => DeliveryStatus::OutForDelivery,
                                        default => DeliveryStatus::Pending,
                                    },
                                    'address_text' => $order->delivery_address_text,
                                    'city' => $order->delivery_city,
                                    'delivered_at' => $order->status === OrderStatus::Delivered
                                        ? fake()->dateTimeBetween('-1 week', 'now')
                                        : null,
                                ]);
                        }
                    });
            });
    }
}
