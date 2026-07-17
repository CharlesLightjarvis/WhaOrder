import { Smartphone } from 'lucide-react';
import ConnectSessionDialog from './connect-session-dialog';

export default function EmptyState() {
    return (
        <div className="flex flex-col items-center rounded-lg border border-dashed p-12 text-center">
            <div className="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-muted">
                <Smartphone className="h-7 w-7 text-muted-foreground" />
            </div>
            <p className="font-medium">Aucun numéro connecté</p>
            <p className="mt-1 max-w-sm text-sm text-muted-foreground">
                Connectez un numéro WhatsApp pour commencer à recevoir et
                traiter les commandes de vos clients directement depuis la
                conversation.
            </p>
            <div className="mt-6">
                <ConnectSessionDialog />
            </div>
        </div>
    );
}
