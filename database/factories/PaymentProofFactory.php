<?php

namespace Database\Factories;

use App\Enums\PaymentProofStatus;
use App\Enums\PaymentProofType;
use App\Models\Order;
use App\Models\PaymentProof;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentProof>
 */
class PaymentProofFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(PaymentProofType::cases());

        return [
            'order_id' => Order::factory(),
            'type' => $type,
            'image_url' => $type === PaymentProofType::Screenshot
                ? 'https://picsum.photos/seed/'.fake()->uuid().'/400/700'
                : null,
            'raw_message' => $type === PaymentProofType::Message
                ? 'J\'ai envoyé le paiement, merci de vérifier.'
                : null,
            'status' => fake()->randomElement(PaymentProofStatus::cases()),
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
