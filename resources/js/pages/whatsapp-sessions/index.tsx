import { useEffect } from 'react';
import { Head, usePage, usePoll } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import WhatsAppSessionController from '@/actions/App/Http/Controllers/WhatsAppSessionController';
import ConnectSessionDialog from './partials/connect-session-dialog';
import EmptyState from './partials/empty-state';
import SessionCard, { PENDING_STATUSES } from './partials/session-card';
import type { WhatsAppSession } from '@/types/whatsapp-session';

type Props = {
    sessions: WhatsAppSession[];
};

export default function WhatsAppSessionIndex() {
    const { sessions } = usePage<Props>().props;

    const hasPendingSession = sessions.some((session) =>
        PENDING_STATUSES.includes(session.status),
    );

    const { start, stop } = usePoll(
        3000,
        { only: ['sessions'] },
        { mode: 'rest', autoStart: false },
    );

    useEffect(() => {
        if (hasPendingSession) {
            start();
        } else {
            stop();
        }

        return stop;
    }, [hasPendingSession]);

    return (
        <>
            <Head title="Connexion WhatsApp" />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div className="flex flex-col space-y-2">
                        <h1 className="text-3xl font-bold tracking-tight">
                            Connexion WhatsApp
                        </h1>
                        <p className="text-muted-foreground">
                            Connectez un ou plusieurs numéros WhatsApp pour
                            recevoir et traiter les commandes.
                        </p>
                    </div>
                    <ConnectSessionDialog />
                </div>

                {sessions.length === 0 ? (
                    <EmptyState />
                ) : (
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {sessions.map((session) => (
                            <SessionCard key={session.id} session={session} />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

WhatsAppSessionIndex.layout = (page: React.ReactNode) => (
    <AppLayout
        breadcrumbs={[
            { title: 'Dashboard', href: dashboard() },
            {
                title: 'WhatsApp',
                href: WhatsAppSessionController.index.url(),
            },
        ]}
    >
        {page}
    </AppLayout>
);
