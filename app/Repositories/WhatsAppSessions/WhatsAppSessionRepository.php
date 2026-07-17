<?php

namespace App\Repositories\WhatsAppSessions;

use App\Models\WhatsAppSession;
use Illuminate\Database\Eloquent\Collection;

interface WhatsAppSessionRepository
{
    /**
     * @return Collection<int, WhatsAppSession>
     */
    public function all(): Collection;

    public function find(string $id): WhatsAppSession;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): WhatsAppSession;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(WhatsAppSession $whatsAppSession, array $data): WhatsAppSession;

    public function delete(WhatsAppSession $whatsAppSession): void;
}
