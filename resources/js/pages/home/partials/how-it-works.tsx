import { motion } from 'framer-motion';
import {
    CreditCard,
    LayoutDashboard,
    MessageCircle,
    PackageSearch,
    Receipt,
} from 'lucide-react';
import { IphoneWhatsapp } from './iphone-whatsapp';

const steps = [
    {
        icon: MessageCircle,
        title: 'Le client écrit sur WhatsApp',
        description:
            'Il pose une question, demande un prix ou passe commande, comme d\'habitude.',
    },
    {
        icon: PackageSearch,
        title: 'WhaOrder détecte la commande',
        description:
            'Produits demandés, quantités et stock disponible sont identifiés automatiquement.',
    },
    {
        icon: CreditCard,
        title: 'Total, localisation et paiement',
        description:
            'Le total est calculé, la localisation récupérée, le mode de paiement choisi.',
    },
    {
        icon: Receipt,
        title: 'Commande, livraison et reçu',
        description:
            'La livraison est créée, le reçu envoyé, le stock et le CRM mis à jour.',
    },
];

export function HowItWorks() {
    return (
        <section className="relative overflow-hidden bg-background py-24 md:py-32">
            <div className="pointer-events-none absolute inset-0">
                <div className="absolute top-1/3 right-0 h-90 w-90 rounded-full bg-primary/5 blur-[130px] dark:bg-primary/8" />
            </div>

            <div className="relative mx-auto grid max-w-6xl items-center gap-16 px-6 md:px-8 lg:grid-cols-2 lg:px-12">
                <motion.div
                    initial={{ opacity: 0, y: 24 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.7 }}
                    className="order-2 lg:order-1"
                >
                    <div className="mb-5 inline-flex items-center gap-2 rounded-full border border-border/40 bg-secondary px-4 py-2 text-xs font-semibold tracking-[0.25em] text-secondary-foreground uppercase backdrop-blur dark:border-border/60">
                        <LayoutDashboard
                            className="h-3.5 w-3.5"
                            aria-hidden="true"
                        />
                        Comment ça marche
                    </div>

                    <h2 className="mb-4 text-3xl font-semibold tracking-tight text-foreground md:text-5xl">
                        Le commerçant reste dans WhatsApp
                    </h2>

                    <p className="mb-10 max-w-lg text-lg text-foreground/60">
                        WhaOrder observe la conversation et transforme chaque
                        échange en commande structurée. Le tableau de bord
                        n'est utilisé que pour superviser.
                    </p>

                    <div className="flex flex-col gap-6">
                        {steps.map((step, index) => (
                            <motion.div
                                key={step.title}
                                initial={{ opacity: 0, x: -16 }}
                                whileInView={{ opacity: 1, x: 0 }}
                                viewport={{ once: true }}
                                transition={{
                                    duration: 0.5,
                                    delay: index * 0.08,
                                }}
                                className="flex items-start gap-4"
                            >
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <step.icon
                                        className="h-5 w-5"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div>
                                    <h3 className="mb-1 text-base font-semibold text-foreground">
                                        {step.title}
                                    </h3>
                                    <p className="text-sm text-foreground/60">
                                        {step.description}
                                    </p>
                                </div>
                            </motion.div>
                        ))}
                    </div>
                </motion.div>

                <motion.div
                    initial={{ opacity: 0, y: 32 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.8 }}
                    className="order-1 lg:order-2"
                >
                    <IphoneWhatsapp />
                </motion.div>
            </div>
        </section>
    );
}
