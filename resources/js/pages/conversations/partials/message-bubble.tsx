import type { ConversationMessage } from '@/types/conversation';

type Props = {
    message: ConversationMessage;
};

export default function MessageBubble({ message }: Props) {
    if (message.role !== 'user' && message.role !== 'assistant') {
        return (
            <div className="mx-auto w-fit rounded-full bg-black/10 px-3 py-1 text-xs text-neutral-600 dark:bg-white/10 dark:text-neutral-300">
                {message.content}
            </div>
        );
    }

    const isCustomer = message.role === 'user';

    return (
        <div
            className={
                isCustomer
                    ? 'mr-auto max-w-[75%] rounded-2xl rounded-tl-sm bg-white px-3 py-2 text-sm text-neutral-800 shadow-sm'
                    : 'ml-auto max-w-[75%] rounded-2xl rounded-tr-sm bg-[#DCF8C6] px-3 py-2 text-sm text-neutral-800 shadow-sm'
            }
        >
            <p className="whitespace-pre-wrap">{message.content}</p>
            <p className="mt-1 text-right text-[10px] text-neutral-500">
                {message.created_at}
            </p>
        </div>
    );
}
