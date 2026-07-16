<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Merchant>
 */
class MerchantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $region = fake()->randomElement([
            ['currency' => Currency::Xof, 'timezone' => 'Africa/Abidjan', 'phone_prefix' => '+225'],
            ['currency' => Currency::Xaf, 'timezone' => 'Africa/Douala', 'phone_prefix' => '+237'],
            ['currency' => Currency::Ngn, 'timezone' => 'Africa/Lagos', 'phone_prefix' => '+234'],
            ['currency' => Currency::Ghs, 'timezone' => 'Africa/Accra', 'phone_prefix' => '+233'],
            ['currency' => Currency::Eur, 'timezone' => 'Europe/Paris', 'phone_prefix' => '+33'],
        ]);

        return [
            'name' => fake()->randomElement([
                'Boutique Aïcha',
                'Chez Fatou',
                'Sacs & Style',
                'Kolo Fashion',
                'Douala Sneakers',
                'Yaoundé Fashion',
                'La Malle de Awa',
                'Bijoux Coco',
            ]),
            'whatsapp_number' => $region['phone_prefix'].' '.fake()->numerify('## ## ## ##'),
            'whatsapp_admin_number' => $region['phone_prefix'].' '.fake()->numerify('## ## ## ##'),
            'currency' => $region['currency'],
            'timezone' => $region['timezone'],
        ];
    }
}
