import { Head, Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import {
    AlertTriangle,
    Home,
    RefreshCw,
    ServerCrash,
    ShieldX,
} from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    status: 403 | 404 | 500 | 503;
};

type ErrorConfig = {
    title: string;
    description: string;
    icon: LucideIcon;
};

const errors: Record<number, ErrorConfig> = {
    403: {
        title: 'Accès interdit',
        description:
            "Vous n'avez pas les permissions nécessaires pour accéder à cette page.",
        icon: ShieldX,
    },
    404: {
        title: 'Page introuvable',
        description:
            "La page que vous recherchez n'existe pas ou a été déplacée.",
        icon: AlertTriangle,
    },
    500: {
        title: 'Erreur serveur',
        description:
            "Une erreur inattendue s'est produite. Nos équipes ont été notifiées.",
        icon: ServerCrash,
    },
    503: {
        title: 'Service indisponible',
        description:
            'Le service est temporairement indisponible. Veuillez réessayer dans quelques instants.',
        icon: RefreshCw,
    },
};

export default function ErrorPage({ status }: Props) {
    const error = errors[status] ?? errors[500];
    const Icon = error.icon;

    return (
        <>
            <Head title={error.title} />

            <div className="flex min-h-screen w-full items-center justify-center bg-background px-6">
                <div className="w-full max-w-md space-y-6 text-center">
                    <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-destructive/10">
                        <Icon className="h-8 w-8 text-destructive" />
                    </div>

                    <div className="space-y-2">
                        <p className="text-sm font-semibold tracking-[0.25em] text-foreground/40 uppercase">
                            Erreur {status}
                        </p>
                        <h1 className="text-2xl font-semibold tracking-tight text-foreground">
                            {error.title}
                        </h1>
                        <p className="text-sm text-foreground/60">
                            {error.description}
                        </p>
                    </div>

                    <div className="flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                        <Button asChild>
                            <Link href="/">
                                <Home className="h-4 w-4" />
                                Retourner à l'accueil
                            </Link>
                        </Button>
                        <Button
                            variant="secondary"
                            onClick={() => window.history.back()}
                        >
                            Page précédente
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
