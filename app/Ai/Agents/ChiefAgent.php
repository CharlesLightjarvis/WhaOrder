<?php

namespace App\Ai\Agents;

use App\Enums\MessageIntent;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

/**
 * Classifies an incoming WhatsApp message so the job can route it to the
 * right specialized agent (OrderAgent or SupportAgent) instead of forcing
 * one agent to handle every kind of message.
 */
#[Provider([Lab::DeepSeek, Lab::Groq])]
#[UseCheapestModel]
class ChiefAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'TXT'
        Tu classes un message WhatsApp envoyé à un commerçant, en une seule intention parmi :
        - order : le client parle de produits, veut acheter ou continuer une commande en cours, pose une question sur le catalogue ou les prix.
        - support : le client parle d'une commande déjà passée : suivi de livraison, retard, réclamation, produit reçu défectueux, remboursement, demande de modification d'une commande déjà confirmée (article, quantité, adresse, paiement), ou toute frustration liée à une commande passée.
        - social : remerciement, salutation seule, ou message qui n'entre clairement dans aucune des deux catégories ci-dessus.

        En cas de doute entre order et social, choisis order.
        En cas de doute entre support et order, choisis support dès que le message évoque une commande ou une livraison déjà en cours ou déjà reçue.
        TXT;
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'intent' => $schema->string()
                ->enum(MessageIntent::class)
                ->description("L'intention du message du client.")
                ->required(),
        ];
    }
}
