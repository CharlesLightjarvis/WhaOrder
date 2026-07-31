<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(fake()->bothify('VAR-####')),
            'name' => fake()->randomElement([
                'Taille S', 'Taille M', 'Taille L', 'Taille XL',
                'Rouge', 'Bleu', 'Noir', 'Blanc',
            ]),
            'price' => fake()->numberBetween(1500, 75000),
            'stock' => fake()->numberBetween(0, 20),
        ];
    }
}
