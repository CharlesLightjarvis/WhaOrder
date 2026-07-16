<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
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
            'category_id' => Category::factory(),
            'name' => fake()->randomElement([
                'Sac à main cuir',
                'Sac bandoulière',
                'Baskets blanches',
                'Sandales dorées',
                'Robe wax',
                'Ensemble pagne',
                'Parfum homme',
                'Crème hydratante',
                'Écouteurs sans fil',
                'Coque téléphone',
                'Montre connectée',
                'Chargeur rapide',
            ]),
            'description' => fake()->sentence(12),
            'price' => fake()->numberBetween(1500, 75000),
            'stock' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }
}
