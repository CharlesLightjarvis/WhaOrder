import { motion } from 'framer-motion';
import {
    Calculator,
    CreditCard,
    MapPin,
    PackageCheck,
    Receipt,
    RefreshCw,
    ScanSearch,
    Sparkles,
    Truck,
    Users,
} from 'lucide-react';

const features = [
    {
        icon: ScanSearch,
        title: 'Détection des produits',
        description:
            'WhaOrder identifie les produits demandés dans la conversation.',
    },
    {
        icon: PackageCheck,
        title: 'Vérification du stock',
        description: 'Le stock disponible est vérifié avant confirmation.',
    },
    {
        icon: Calculator,
        title: 'Calcul du total',
        description:
            'Le montant total est calculé automatiquement selon la quantité.',
    },
    {
        icon: MapPin,
        title: 'Localisation du client',
        description:
            "L'adresse de livraison est récupérée directement depuis WhatsApp.",
    },
    {
        icon: CreditCard,
        title: 'Choix du paiement',
        description: 'Le client choisit son mode de paiement préféré.',
    },
    {
        icon: Truck,
        title: 'Création de la livraison',
        description: 'La livraison est créée et affectée automatiquement.',
    },
    {
        icon: Receipt,
        title: 'Reçu automatique',
        description: 'Un reçu est envoyé au client après confirmation.',
    },
    {
        icon: RefreshCw,
        title: 'Mise à jour du stock',
        description: 'Le stock est ajusté en temps réel après chaque vente.',
    },
    {
        icon: Users,
        title: 'Classement CRM',
        description: 'Chaque client est classé et suivi dans votre CRM.',
    },
];

export function Features() {
    return (
        <section className="relative bg-muted/30 py-24 md:py-32 dark:bg-foreground/2">
            <div className="mx-auto max-w-6xl px-6 md:px-8 lg:px-12">
                <motion.div
                    initial={{ opacity: 0, y: 24 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.7 }}
                    className="mx-auto mb-16 max-w-2xl text-center"
                >
                    <div className="mb-5 inline-flex items-center gap-2 rounded-full border border-border/40 bg-secondary px-4 py-2 text-xs font-semibold tracking-[0.25em] text-secondary-foreground uppercase backdrop-blur dark:border-border/60">
                        <Sparkles className="h-3.5 w-3.5" aria-hidden="true" />
                        Fonctionnalités
                    </div>

                    <h2 className="mb-4 text-3xl font-semibold tracking-tight text-foreground md:text-5xl">
                        Tout ce qu'il faut pour vendre sur WhatsApp
                    </h2>

                    <p className="mx-auto max-w-xl text-lg text-foreground/60">
                        De la première question du client au reçu de
                        livraison, WhaOrder automatise chaque étape.
                    </p>
                </motion.div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {features.map((feature, index) => (
                        <motion.div
                            key={feature.title}
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{
                                duration: 0.5,
                                delay: (index % 3) * 0.06,
                            }}
                            className="rounded-2xl border border-border/40 bg-background/60 p-6 backdrop-blur-sm dark:border-border/50 dark:bg-background/50"
                        >
                            <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <feature.icon
                                    className="h-5 w-5"
                                    aria-hidden="true"
                                />
                            </div>
                            <h3 className="mb-1.5 text-base font-semibold text-foreground">
                                {feature.title}
                            </h3>
                            <p className="text-sm text-foreground/60">
                                {feature.description}
                            </p>
                        </motion.div>
                    ))}
                </div>
            </div>
        </section>
    );
}
