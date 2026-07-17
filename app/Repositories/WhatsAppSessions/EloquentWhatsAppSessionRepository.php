<?php

namespace App\Repositories\WhatsAppSessions;

use App\Models\WhatsAppSession;
use Illuminate\Database\Eloquent\Collection;

class EloquentWhatsAppSessionRepository implements WhatsAppSessionRepository
{
    public function all(): Collection
    {
        return WhatsAppSession::query()->latest()->get();
    }

    public function find(string $id): WhatsAppSession
    {
        return WhatsAppSession::query()->findOrFail($id);
    }

    public function create(array $data): WhatsAppSession
    {
        return WhatsAppSession::query()->create($data);
    }

    public function update(WhatsAppSession $whatsAppSession, array $data): WhatsAppSession
    {
        $whatsAppSession->update($data);

        return $whatsAppSession;
    }

    public function delete(WhatsAppSession $whatsAppSession): void
    {
        $whatsAppSession->delete();
    }
}
