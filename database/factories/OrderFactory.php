<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(2000, 100000);
        $deliveryFee = fake()->randomElement([0, 500, 1000, 1500]);

        return [
            'merchant_id' => Merchant::factory(),
            'customer_id' => Customer::factory(),
            'conversation_id' => null,
            'status' => fake()->randomElement(OrderStatus::cases()),
            'payment_status' => fake()->randomElement(PaymentStatus::cases()),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'delivery_address_text' => fake()->streetAddress(),
            'delivery_city' => fake()->randomElement([
                'Douala', 'Yaoundé', 'Abidjan', 'Lagos', 'Accra',
            ]),
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $subtotal + $deliveryFee,
        ];
    }
}
