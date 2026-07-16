<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'label' => fake()->randomElement(['Domicile', 'Bureau', null]),
            'full_name' => fake()->name(),
            'phone' => fake()->randomElement(['+225', '+237', '+234', '+233', '+33']).' '.fake()->numerify('## ## ## ##'),
            'line1' => fake()->streetAddress(),
            'line2' => null,
            'city' => fake()->randomElement([
                'Douala', 'Yaoundé', 'Abidjan', 'Lagos', 'Accra', 'Paris',
            ]),
            'country' => fake()->randomElement(['CM', 'CI', 'NG', 'GH', 'FR']),
            'is_default' => true,
        ];
    }
}
