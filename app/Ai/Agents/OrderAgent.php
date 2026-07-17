<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CalculateTotalTool;
use App\Ai\Tools\CheckStockTool;
use App\Ai\Tools\FinalizeOrderTool;
use App\Ai\Tools\SearchProductTool;
use App\Models\Conversation;
use App\Models\Merchant;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\RemembersConversations as RemembersConversationsContract;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

class OrderAgent implements Agent, HasTools, RemembersConversationsContract
{
    use Promptable, RemembersConversations;

    public function __construct(
        private readonly Merchant $merchant,
        private readonly Conversation $conversation,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<TXT
        Tu es l'assistant WhatsApp du commerçant "{$this->merchant->name}", qui vend en {$this->merchant->currency->value}.

        Ton rôle : aider le client à passer commande directement dans la conversation WhatsApp.

        Règles strictes :
        - N'invente JAMAIS un produit, un prix ou un stock. Utilise toujours les outils pour vérifier le catalogue réel.
        - Utilise chercher_produit dès que le client mentionne un article, pour retrouver son identifiant exact.
        - Utilise verifier_stock avant de confirmer qu'une quantité est disponible.
        - Utilise calculer_total à chaque fois que le panier change (nouvel article, quantité, adresse, moyen de paiement). Renvoie-lui TOUJOURS la liste complète des articles du panier, pas seulement les nouveaux.
        - Tant qu'il manque un article, l'adresse de livraison ou le moyen de paiement, continue à poser des questions au client. NE finalise PAS.
        - N'appelle finaliser_commande que lorsque le panier est complet ET que le client a explicitement confirmé vouloir passer commande.
        - Réponds toujours en français, sur un ton amical et concis, adapté à WhatsApp (pas de longs paragraphes).
        TXT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return array<int, Tool>
     */
    public function tools(): iterable
    {
        return [
            new SearchProductTool($this->merchant),
            new CheckStockTool,
            new CalculateTotalTool($this->merchant, $this->conversation),
            new FinalizeOrderTool($this->merchant, $this->conversation),
        ];
    }
}
