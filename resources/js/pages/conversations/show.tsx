import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import ConversationController from '@/actions/App/Http/Controllers/ConversationController';
import MessageBubble from './partials/message-bubble';
import type { Conversation, ConversationMessage } from '@/types/conversation';

type Props = {
    conversation: Conversation;
    messages: ConversationMessage[];
};

export default function ConversationShow() {
    const { conversation, messages } = usePage<Props>().props;

    return (
        <>
            <Head title="Conversation" />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div className="flex flex-col space-y-1">
                        <h1 className="text-3xl font-bold tracking-tight">
                            {conversation.customer?.name ??
                                conversation.customer?.whatsapp_number ??
                                'Conversation'}
                        </h1>
                        <p className="text-muted-foreground">
                            {conversation.customer?.whatsapp_number}
                        </p>
                    </div>
                    <Badge>{conversation.status_label}</Badge>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Fil de discussion</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex max-h-[600px] flex-col gap-2 overflow-y-auto rounded-lg bg-[#E5DDD5] p-4 dark:bg-neutral-800">
                                {messages.length === 0 && (
                                    <p className="text-center text-sm text-muted-foreground">
                                        Aucun message pour cette conversation.
                                    </p>
                                )}
                                {messages.map((message) => (
                                    <MessageBubble
                                        key={message.id}
                                        message={message}
                                    />
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Commande en cours</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {conversation.draft_order ? (
                                <pre className="overflow-x-auto rounded-md bg-muted p-3 text-xs">
                                    {JSON.stringify(
                                        conversation.draft_order,
                                        null,
                                        2,
                                    )}
                                </pre>
                            ) : (
                                <p className="text-sm text-muted-foreground italic">
                                    Aucune commande en cours de construction.
                                </p>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

ConversationShow.layout = (page: React.ReactNode) => (
    <AppLayout
        breadcrumbs={[
            { title: 'Dashboard', href: dashboard() },
            {
                title: 'Conversations',
                href: ConversationController.index.url(),
            },
            { title: 'Détail', href: '#' },
        ]}
    >
        {page}
    </AppLayout>
);
