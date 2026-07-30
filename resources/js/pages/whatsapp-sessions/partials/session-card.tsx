import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Trash2Icon } from 'lucide-react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import WhatsAppSessionController from '@/actions/App/Http/Controllers/WhatsAppSessionController';
import type {
    WhatsAppSession,
    WhatsAppSessionStatus,
} from '@/types/whatsapp-session';

type Props = {
    session: WhatsAppSession;
};

const statusVariant: Record<
    WhatsAppSessionStatus,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    STOPPED: 'outline',
    STARTING: 'secondary',
    SCAN_QR_CODE: 'secondary',
    WORKING: 'default',
    FAILED: 'destructive',
};

export const PENDING_STATUSES: WhatsAppSessionStatus[] = [
    'STOPPED',
    'STARTING',
    'SCAN_QR_CODE',
];

export default function SessionCard({ session }: Props) {
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState(false);

    const isPending = PENDING_STATUSES.includes(session.status);
    const justScanned =
        session.status === 'STARTING' && Boolean(session.qr_code);

    function handleDelete() {
        setDeleting(true);
        router.delete(WhatsAppSessionController.destroy.url(session.id), {
            onFinish: () => {
                setDeleting(false);
                setDeleteOpen(false);
            },
        });
    }

    return (
        <>
            <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0">
                    <CardTitle>{session.label}</CardTitle>
                    <Badge variant={statusVariant[session.status]}>
                        {justScanned
                            ? 'Connexion en cours…'
                            : session.status_label}
                    </Badge>
                </CardHeader>
                <CardContent className="space-y-4">
                    {session.status === 'SCAN_QR_CODE' && session.qr_code && (
                        <div className="flex flex-col items-center gap-2">
                            <img
                                src={session.qr_code}
                                alt="QR code WhatsApp"
                                className="size-48 rounded-md border"
                            />
                            <p className="text-center text-sm text-muted-foreground">
                                Ouvrez WhatsApp sur le téléphone à connecter →
                                Appareils liés → Scanner ce code.
                            </p>
                        </div>
                    )}

                    {justScanned && (
                        <p className="text-center text-sm text-muted-foreground">
                            QR code scanné ! Finalisation de la connexion…
                        </p>
                    )}

                    {isPending && !session.qr_code && !justScanned && (
                        <p className="text-sm text-muted-foreground">
                            Préparation de la session…
                        </p>
                    )}

                    {session.status === 'WORKING' && (
                        <div className="flex items-center gap-3">
                            <Avatar className="size-10">
                                {session.profile_picture_url && (
                                    <AvatarImage
                                        src={session.profile_picture_url}
                                        alt={
                                            session.profile_name ??
                                            'Photo de profil'
                                        }
                                    />
                                )}
                                <AvatarFallback>
                                    {session.profile_name
                                        ? session.profile_name
                                              .charAt(0)
                                              .toUpperCase()
                                        : '?'}
                                </AvatarFallback>
                            </Avatar>
                            <div>
                                {session.profile_name && (
                                    <p className="font-medium">
                                        {session.profile_name}
                                    </p>
                                )}
                                <p className="text-sm text-muted-foreground">
                                    {session.phone_number ?? '—'}
                                </p>
                            </div>
                        </div>
                    )}

                    {session.status === 'FAILED' && (
                        <p className="text-sm text-destructive">
                            La connexion a échoué. Supprimez cette session et
                            réessayez.
                        </p>
                    )}

                    <div className="flex justify-end">
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setDeleteOpen(true)}
                            className="text-destructive hover:text-destructive"
                        >
                            <Trash2Icon className="size-4" />
                            Déconnecter
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <Dialog open={deleteOpen} onOpenChange={setDeleteOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Déconnecter cette session</DialogTitle>
                        <DialogDescription>
                            Êtes-vous sûr de vouloir déconnecter{' '}
                            <strong>"{session.label}"</strong> ? Le commerçant
                            devra scanner un nouveau QR code pour reconnecter ce
                            numéro.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setDeleteOpen(false)}
                            disabled={deleting}
                        >
                            Annuler
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            onClick={handleDelete}
                            disabled={deleting}
                        >
                            {deleting ? 'Déconnexion…' : 'Déconnecter'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
