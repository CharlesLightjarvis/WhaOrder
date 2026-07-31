<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Repositories\Conversations\ConversationRepository;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Ai\Models\ConversationMessage;

class ConversationController extends Controller
{
    public function __construct(
        private readonly ConversationRepository $repository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('conversations/index', [
            'conversations' => $this->repository->paginate(15)->through(
                fn (Conversation $conversation) => ConversationResource::make($conversation),
            ),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversation $conversation): Response
    {
        $conversation = $this->repository->find($conversation->id);

        $messages = $conversation->agent_conversation_id
            ? ConversationMessage::query()
                ->where('conversation_id', $conversation->agent_conversation_id)
                ->orderBy('created_at')
                ->get(['id', 'role', 'content', 'created_at'])
            : collect();

        return Inertia::render('conversations/show', [
            'conversation' => ConversationResource::make($conversation),
            'messages' => $messages->map(fn (ConversationMessage $message) => [
                'id' => $message->getKey(),
                'role' => (string) $message->getAttribute('role'),
                'content' => (string) $message->getAttribute('content'),
                'created_at' => Date::parse((string) $message->getAttribute('created_at'))->toDateTimeString(),
            ]),
        ]);
    }
}
