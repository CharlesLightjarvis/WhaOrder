<?php

namespace App\Actions\Customers;

use App\Models\Conversation;

class SummarizeCustomerContext
{
    /**
     * Summarize what's known about a customer so any agent can recognize
     * returning customers instead of treating every message as a stranger's.
     */
    public function handle(Conversation $conversation): string
    {
        $customer = $conversation->customer;

        if (! $customer) {
            return "Aucune information sur ce client, c'est probablement son premier message.";
        }

        $lines = [];

        $lines[] = $customer->name
            ? "Le client s'appelle {$customer->name}."
            : 'Le nom du client est inconnu.';

        $orderCount = $customer->orders()->count();

        if ($orderCount === 0) {
            $lines[] = "C'est un nouveau client, il n'a jamais commandé.";

            return implode("\n", $lines);
        }

        $lastOrderDate = $customer->last_order_at?->translatedFormat('d/m/Y') ?? 'date inconnue';
        $lines[] = "Client existant : {$orderCount} commande(s) précédente(s), la dernière le {$lastOrderDate}.";

        $address = $customer->addresses()->where('is_default', true)->first()
            ?? $customer->addresses()->latest()->first();

        if ($address) {
            $lines[] = sprintf(
                'Adresse de livraison connue : %s%s, %s.',
                $address->line1,
                $address->line2 ? ", {$address->line2}" : '',
                $address->city,
            );
        }

        return implode("\n", $lines);
    }
}
