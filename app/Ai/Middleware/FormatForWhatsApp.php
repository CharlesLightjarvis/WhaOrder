<?php

namespace App\Ai\Middleware;

use Closure;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;

class FormatForWhatsApp
{
    /**
     * Handle the incoming prompt.
     */
    public function handle(AgentPrompt $prompt, Closure $next)
    {
        return $next($prompt)->then(function (AgentResponse $response) {
            $response->text = $this->toWhatsAppFormatting($response->text);
        });
    }

    /**
     * Convert markdown-style formatting the model might still produce into
     * WhatsApp's own syntax, regardless of how well it followed the prompt.
     */
    private function toWhatsAppFormatting(string $text): string
    {
        // **bold** or __bold__ -> *bold* (WhatsApp only recognizes single markers).
        $text = preg_replace('/\*\*(.+?)\*\*/s', '*$1*', $text);
        $text = preg_replace('/__(.+?)__/s', '*$1*', $text);

        // Markdown bullets ("* item" / "+ item") at line start -> WhatsApp-safe "- item".
        return preg_replace('/^[ \t]*[*+][ \t]+/m', '- ', $text);
    }
}
