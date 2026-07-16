<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phonePrefix = fake()->randomElement(['+225', '+237', '+234', '+233', '+33']);

        return [
            'merchant_id' => Merchant::factory(),
            'whatsapp_number' => $phonePrefix.' '.fake()->numerify('## ## ## ##'),
            'name' => fake()->name(),
            'notes' => null,
            'last_order_at' => fake()->optional()->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
