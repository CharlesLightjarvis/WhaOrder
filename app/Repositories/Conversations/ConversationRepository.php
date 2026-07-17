<?php

namespace App\Repositories\Conversations;

use App\Models\Conversation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ConversationRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(string $id): Conversation;
}
