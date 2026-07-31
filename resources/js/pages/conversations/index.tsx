import { Head, usePage } from '@inertiajs/react';
import ConversationController from '@/actions/App/Http/Controllers/ConversationController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { Conversation } from '@/types/conversation';
import type { Paginated } from '@/types/pagination';
import ConversationList from './partials/conversation-list';

type Props = {
    conversations: Paginated<Conversation>;
};

export default function ConversationIndex() {
    const { conversations } = usePage<Props>().props;

    return (
        <>
            <Head title="Conversations" />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-col space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Conversations
                    </h1>
                    <p className="text-muted-foreground">
                        Supervisez les échanges WhatsApp pris en charge par
                        l'assistant.
                    </p>
                </div>

                <ConversationList conversations={conversations} />
            </div>
        </>
    );
}

ConversationIndex.layout = (page: React.ReactNode) => (
    <AppLayout
        breadcrumbs={[
            { title: 'Dashboard', href: dashboard() },
            {
                title: 'Conversations',
                href: ConversationController.index.url(),
            },
        ]}
    >
        {page}
    </AppLayout>
);
