<?php

namespace App\Repositories\Conversations;

use App\Models\Conversation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentConversationRepository implements ConversationRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Conversation::query()
            ->with('customer:id,name,whatsapp_number')
            ->latest('last_message_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(string $id): Conversation
    {
        return Conversation::query()
            ->with('customer:id,name,whatsapp_number')
            ->findOrFail($id);
    }
}
