<?php

namespace App\Repositories\Conversations;

use App\Models\Conversation;
use Illuminate\Pagination\LengthAwarePaginator;

interface ConversationRepository
{
    /** @return LengthAwarePaginator<int, Conversation> */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(string $id): Conversation;
}
