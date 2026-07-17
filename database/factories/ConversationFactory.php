<?php

namespace Database\Factories;

use App\Enums\ConversationStatus;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
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
            'customer_id' => Customer::factory(),
            'agent_conversation_id' => null,
            'status' => fake()->randomElement(ConversationStatus::cases()),
            'draft_order' => null,
            'last_message_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
