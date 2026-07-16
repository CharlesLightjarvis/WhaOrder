import { motion, AnimatePresence } from 'framer-motion';
import { Plus } from 'lucide-react';
import { useState } from 'react';

const questions = [
    {
        question: 'Dois-je installer une nouvelle application ?',
        reponse:
            'Non. Vos clients continuent de vous écrire sur WhatsApp comme avant. WhaOrder observe la conversation et transforme les échanges en commandes, sans rien changer pour vos clients.',
    },
    {
        question: 'WhaOrder fonctionne-t-il pour plusieurs commerçants ?',
        reponse:
            "Oui, WhaOrder est multi-tenant : chaque commerçant dispose de son propre espace, ses produits, son stock et ses clients, complètement séparés des autres.",
    },
    {
        question: 'Comment le stock est-il mis à jour ?',
        reponse:
            'Dès qu\'une commande est confirmée, le stock du produit concerné est automatiquement décrémenté et reste synchronisé avec votre tableau de bord.',
    },
    {
        question: 'Comment WhaOrder vérifie le paiement ?',
        reponse:
            "Le client choisit son mode de paiement (Mobile Money, cash à la livraison, etc.) directement dans la conversation, et la commande n'est confirmée qu'une fois le paiement validé.",
    },
    {
        question: "Puis-je suivre mes commandes en dehors de WhatsApp ?",
        reponse:
            'Oui, un tableau de bord vous permet de superviser vos commandes, votre stock et vos clients en un coup d\'œil, sans jamais quitter WhatsApp pour votre activité quotidienne.',
    },
    {
        question: 'Que se passe-t-il si un client relance plusieurs fois ?',
        reponse:
            "Chaque conversation est centralisée et classée dans votre CRM, avec l'historique des échanges et des commandes, pour éviter les doublons et les oublis.",
    },
] as const;

export default function FAQ() {
    const [ouvert, setOuvert] = useState<number | null>(null);

    return (
        <section className="relative bg-muted/30 py-24 md:py-32 dark:bg-foreground/2">
            <div className="pointer-events-none absolute inset-0 overflow-hidden">
                <div className="absolute bottom-0 left-1/2 h-95 w-95 -translate-x-1/2 rounded-full bg-foreground/2 blur-[130px] dark:bg-foreground/4" />
            </div>

            <div className="relative mx-auto max-w-3xl px-6 md:px-8 lg:px-12">
                {/* en-tête */}
                <motion.div
                    initial={{ opacity: 0, y: 24 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.7 }}
                    className="mb-14 text-center"
                >
                    <div className="mb-5 inline-flex items-center gap-2 rounded-full border border-border/40 bg-secondary px-4 py-2 text-xs font-semibold tracking-[0.25em] text-secondary-foreground uppercase backdrop-blur dark:border-border/60 dark:bg-secondary">
                        Questions fréquentes
                    </div>

                    <h2 className="mb-4 text-3xl font-semibold tracking-tight text-foreground md:text-5xl">
                        Toutes vos questions, répondues
                    </h2>

                    <p className="mx-auto max-w-xl text-lg text-foreground/60">
                        Retrouvez les réponses aux questions les plus
                        fréquentes sur le fonctionnement de WhaOrder pour
                        votre activité.
                    </p>
                </motion.div>

                {/* accordéon */}
                <motion.div
                    initial={{ opacity: 0, y: 16 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.6, delay: 0.15 }}
                    className="flex flex-col gap-3"
                >
                    {questions.map((item, index) => (
                        <div
                            key={index}
                            className="overflow-hidden rounded-2xl border border-border/40 bg-background/60 backdrop-blur-sm dark:border-border/50 dark:bg-background/50"
                        >
                            <button
                                type="button"
                                onClick={() =>
                                    setOuvert(ouvert === index ? null : index)
                                }
                                className="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
                                aria-expanded={ouvert === index}
                            >
                                <span className="text-sm font-semibold text-foreground md:text-base">
                                    {item.question}
                                </span>

                                <span
                                    className={`flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-border/40 bg-background/60 text-foreground/60 transition-transform duration-300 dark:border-border/50 dark:bg-background/40 ${
                                        ouvert === index ? 'rotate-45' : ''
                                    }`}
                                    aria-hidden="true"
                                >
                                    <Plus className="h-4 w-4" />
                                </span>
                            </button>

                            <AnimatePresence initial={false}>
                                {ouvert === index && (
                                    <motion.div
                                        key="content"
                                        initial={{ height: 0, opacity: 0 }}
                                        animate={{ height: 'auto', opacity: 1 }}
                                        exit={{ height: 0, opacity: 0 }}
                                        transition={{
                                            duration: 0.3,
                                            ease: 'easeInOut',
                                        }}
                                    >
                                        <div className="border-t border-border/30 px-6 pt-4 pb-5 text-sm leading-relaxed text-foreground/60">
                                            {item.reponse}
                                        </div>
                                    </motion.div>
                                )}
                            </AnimatePresence>
                        </div>
                    ))}
                </motion.div>
            </div>
        </section>
    );
}
