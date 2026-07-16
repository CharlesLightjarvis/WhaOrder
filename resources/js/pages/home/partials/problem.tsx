import { motion } from 'framer-motion';
import {
    AlertTriangle,
    EyeOff,
    MapPin,
    PackageX,
    RefreshCw,
    ShieldAlert,
    Truck,
} from 'lucide-react';

const painPoints = [
    {
        icon: AlertTriangle,
        title: 'Commandes oubliées',
        description:
            'Perdues au milieu des conversations, elles ne sont jamais traitées.',
    },
    {
        icon: MapPin,
        title: 'Adresse imprécise',
        description:
            "Les livreurs tournent en rond faute d'une localisation claire.",
    },
    {
        icon: ShieldAlert,
        title: 'Paiement non vérifié',
        description:
            "Impossible de confirmer un paiement avant d'expédier la commande.",
    },
    {
        icon: PackageX,
        title: 'Rupture de stock',
        description:
            'Un produit vendu deux fois faute de suivi du stock en temps réel.',
    },
    {
        icon: RefreshCw,
        title: 'Client relancé plusieurs fois',
        description:
            'Les mêmes questions reviennent sans cesse, sans historique centralisé.',
    },
    {
        icon: Truck,
        title: 'Livraison mal affectée',
        description: 'Le mauvais colis part chez le mauvais client.',
    },
];

export function Problem() {
    return (
        <section className="relative bg-muted/30 py-24 md:py-32 dark:bg-foreground/2">
            <div className="pointer-events-none absolute inset-0 overflow-hidden">
                <div className="absolute top-0 left-1/2 h-90 w-90 -translate-x-1/2 rounded-full bg-foreground/2 blur-[130px] dark:bg-foreground/4" />
            </div>

            <div className="relative mx-auto max-w-6xl px-6 md:px-8 lg:px-12">
                <motion.div
                    initial={{ opacity: 0, y: 24 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.7 }}
                    className="mx-auto mb-16 max-w-2xl text-center"
                >
                    <div className="mb-5 inline-flex items-center gap-2 rounded-full border border-border/40 bg-secondary px-4 py-2 text-xs font-semibold tracking-[0.25em] text-secondary-foreground uppercase backdrop-blur dark:border-border/60">
                        <EyeOff className="h-3.5 w-3.5" aria-hidden="true" />
                        Le problème
                    </div>

                    <h2 className="mb-4 text-3xl font-semibold tracking-tight text-foreground md:text-5xl">
                        Les commandes WhatsApp deviennent ingérables
                    </h2>

                    <p className="mx-auto max-w-xl text-lg text-foreground/60">
                        Dès que le volume augmente, gérer les commandes à la
                        main sur WhatsApp devient une source d'erreurs et de
                        clients frustrés.
                    </p>
                </motion.div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {painPoints.map((point, index) => (
                        <motion.div
                            key={point.title}
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.5, delay: index * 0.05 }}
                            className="rounded-2xl border border-border/40 bg-background/60 p-6 backdrop-blur-sm dark:border-border/50 dark:bg-background/50"
                        >
                            <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-destructive/10 text-destructive">
                                <point.icon
                                    className="h-5 w-5"
                                    aria-hidden="true"
                                />
                            </div>
                            <h3 className="mb-1.5 text-base font-semibold text-foreground">
                                {point.title}
                            </h3>
                            <p className="text-sm text-foreground/60">
                                {point.description}
                            </p>
                        </motion.div>
                    ))}
                </div>
            </div>
        </section>
    );
}
