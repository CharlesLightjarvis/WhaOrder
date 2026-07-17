<?php

namespace Database\Factories;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status' => fake()->randomElement(DeliveryStatus::cases()),
            'address_text' => fake()->streetAddress(),
            'city' => fake()->randomElement([
                'Douala', 'Yaoundé', 'Abidjan', 'Lagos', 'Accra',
            ]),
            'scheduled_at' => fake()->dateTimeBetween('now', '+3 days'),
            'delivered_at' => null,
        ];
    }
}
