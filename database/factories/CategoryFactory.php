<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'merchant_id' => Merchant::factory(),
            'name' => fake()->randomElement([
                'Sacs',
                'Chaussures',
                'Vêtements',
                'Cosmétiques',
                'Accessoires',
                'Électronique',
                'Parfums',
                'Bijoux',
            ]),
        ];
    }
}
