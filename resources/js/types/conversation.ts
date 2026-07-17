export type ConversationStatus = 'active' | 'completed' | 'abandoned';

export type ConversationMessage = {
    id: string;
    role: string;
    content: string;
    created_at: string;
};

export type Conversation = {
    id: string;
    status: ConversationStatus;
    status_label: string;
    draft_order: Record<string, unknown> | null;
    last_message_at: string | null;
    customer?: {
        id: string;
        name: string | null;
        whatsapp_number: string;
    };
    created_at: string;
};
