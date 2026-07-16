'use client';
import { useState } from 'react';
import { motion, type Variants } from 'framer-motion';
import {
    MapPin,
    Phone,
    Mail,
    Clock,
    BookOpen,
    Building2,
    Handshake,
    ArrowRight,
    Send,
    CheckCircle2,
} from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

/* ── données ─────────────────────────────────────────────── */

const infos = [
    {
        icon: MapPin,
        label: 'Adresse',
        value: 'Paris, France\nCommunauté internationale',
    },
    { icon: Phone, label: 'Téléphone', value: '+33 1 00 00 00 00' },
    { icon: Mail, label: 'Email', value: 'contact@pmindfull.com' },
    {
        icon: Clock,
        label: 'Disponibilité',
        value: 'Lun – Ven : 9h00 – 18h00\nRéponse sous 24h',
    },
];

const quickCards = [
    {
        icon: BookOpen,
        titre: 'Nos formations',
        description:
            'Questions sur nos cours de mindfulness, chakras, yoga ou sophrologie.',
        cta: 'Voir les formations',
    },
    {
        icon: Building2,
        titre: 'Devenir formateur',
        description:
            'Vous êtes praticien du bien-être et souhaitez publier vos cours sur pMindfull.',
        cta: 'Déposer ma candidature',
    },
    {
        icon: Handshake,
        titre: 'Partenariat',
        description:
            "Entreprise, institution ou association ? Discutons d'une collaboration.",
        cta: 'Nous écrire',
    },
];

const subjects = [
    'Renseignement formation',
    'Devenir formateur',
    'Problème technique',
    'Partenariat',
    'Autre',
];

/* ── variants ────────────────────────────────────────────── */

const fadeUp: Variants = {
    hidden: { opacity: 0, y: 24 },
    visible: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.6, ease: 'easeOut' },
    },
};

/* ── composant ───────────────────────────────────────────── */

export default function Contact() {
    const [form, setForm] = useState({
        nom: '',
        email: '',
        objet: '',
        message: '',
    });
    const [sent, setSent] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        // TODO: connecter à un vrai endpoint
        setSent(true);
    };

    return (
        <div className="relative min-h-screen">
            {/* blob décoratif */}
            <div className="pointer-events-none absolute inset-0 overflow-hidden">
                <div className="absolute top-0 left-1/2 h-[600px] w-[600px] -translate-x-1/2 rounded-full bg-primary/[0.03] blur-[180px] dark:bg-primary/[0.06]" />
            </div>

            <div className="relative mx-auto max-w-7xl px-4 py-20 md:px-6 md:py-28 lg:px-8">
                {/* ── En-tête ──────────────────────────────────────── */}
                <motion.div
                    initial="hidden"
                    animate="visible"
                    variants={fadeUp}
                    className="mb-16 text-center"
                >
                    <div className="mb-5 inline-flex items-center gap-2 rounded-full border border-border/40 bg-secondary px-4 py-2 text-xs font-semibold tracking-[0.25em] text-secondary-foreground uppercase backdrop-blur">
                        Parlons-nous
                    </div>
                    <h1 className="mb-4 text-4xl font-semibold tracking-tight text-foreground md:text-5xl">
                        Contactez-nous
                    </h1>
                    <p className="mx-auto max-w-xl text-lg text-foreground/55">
                        Une question sur nos formations, votre candidature
                        formateur ou un projet de partenariat ? Nous vous
                        répondons sous 24h.
                    </p>
                </motion.div>

                {/* ── Cartes rapides ───────────────────────────────── */}
                <motion.div
                    initial="hidden"
                    animate="visible"
                    variants={{
                        hidden: {},
                        visible: { transition: { staggerChildren: 0.1 } },
                    }}
                    className="mb-16 grid gap-5 sm:grid-cols-3"
                >
                    {quickCards.map((card) => {
                        const Icon = card.icon;
                        return (
                            <motion.div key={card.titre} variants={fadeUp}>
                                <Card className="group h-full cursor-pointer border-border/40 bg-background/60 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:border-border/60 hover:shadow-lg dark:border-border/50 dark:bg-background/50">
                                    <CardContent className="p-6">
                                        <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                                            <Icon className="h-5 w-5 text-primary" />
                                        </div>
                                        <h3 className="mb-2 font-semibold text-foreground">
                                            {card.titre}
                                        </h3>
                                        <p className="mb-5 text-sm leading-relaxed text-foreground/55">
                                            {card.description}
                                        </p>
                                        <span className="flex items-center gap-1.5 text-xs font-semibold tracking-[0.12em] text-foreground/50 uppercase transition-all group-hover:gap-2.5 group-hover:text-foreground">
                                            {card.cta}
                                            <ArrowRight className="h-3 w-3" />
                                        </span>
                                    </CardContent>
                                </Card>
                            </motion.div>
                        );
                    })}
                </motion.div>

                {/* ── Formulaire + Infos ───────────────────────────── */}
                <div className="grid gap-10 lg:grid-cols-[1fr_380px] xl:grid-cols-[1fr_420px]">
                    {/* Formulaire */}
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true }}
                        variants={fadeUp}
                    >
                        <Card className="border-border/40 bg-background/60 backdrop-blur-sm dark:border-border/50 dark:bg-background/50">
                            <CardContent className="p-8 md:p-10">
                                {!sent ? (
                                    <>
                                        <h2 className="mb-8 text-xl font-semibold text-foreground">
                                            Envoyer un message
                                        </h2>
                                        <form
                                            onSubmit={handleSubmit}
                                            className="space-y-6"
                                        >
                                            {/* nom + email */}
                                            <div className="grid gap-5 sm:grid-cols-2">
                                                <div className="space-y-2">
                                                    <label className="text-xs font-semibold tracking-[0.15em] text-foreground/45 uppercase">
                                                        Nom complet
                                                    </label>
                                                    <input
                                                        type="text"
                                                        required
                                                        value={form.nom}
                                                        onChange={(e) =>
                                                            setForm({
                                                                ...form,
                                                                nom: e.target
                                                                    .value,
                                                            })
                                                        }
                                                        placeholder="Jean Dupont"
                                                        className="w-full rounded-xl border border-border/50 bg-background/80 px-4 py-3 text-sm text-foreground transition-colors placeholder:text-foreground/30 focus:border-primary/50 focus:ring-1 focus:ring-primary/30 focus:outline-none"
                                                    />
                                                </div>
                                                <div className="space-y-2">
                                                    <label className="text-xs font-semibold tracking-[0.15em] text-foreground/45 uppercase">
                                                        Email
                                                    </label>
                                                    <input
                                                        type="email"
                                                        required
                                                        value={form.email}
                                                        onChange={(e) =>
                                                            setForm({
                                                                ...form,
                                                                email: e.target
                                                                    .value,
                                                            })
                                                        }
                                                        placeholder="vous@email.com"
                                                        className="w-full rounded-xl border border-border/50 bg-background/80 px-4 py-3 text-sm text-foreground transition-colors placeholder:text-foreground/30 focus:border-primary/50 focus:ring-1 focus:ring-primary/30 focus:outline-none"
                                                    />
                                                </div>
                                            </div>

                                            {/* objet */}
                                            <div className="space-y-2">
                                                <label className="text-xs font-semibold tracking-[0.15em] text-foreground/45 uppercase">
                                                    Objet
                                                </label>
                                                <select
                                                    required
                                                    value={form.objet}
                                                    onChange={(e) =>
                                                        setForm({
                                                            ...form,
                                                            objet: e.target
                                                                .value,
                                                        })
                                                    }
                                                    className="w-full rounded-xl border border-border/50 bg-background/80 px-4 py-3 text-sm text-foreground transition-colors focus:border-primary/50 focus:ring-1 focus:ring-primary/30 focus:outline-none"
                                                >
                                                    <option value="" disabled>
                                                        Sélectionner un objet
                                                    </option>
                                                    {subjects.map((s) => (
                                                        <option
                                                            key={s}
                                                            value={s}
                                                        >
                                                            {s}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>

                                            {/* message */}
                                            <div className="space-y-2">
                                                <label className="text-xs font-semibold tracking-[0.15em] text-foreground/45 uppercase">
                                                    Message
                                                </label>
                                                <textarea
                                                    required
                                                    rows={6}
                                                    value={form.message}
                                                    onChange={(e) =>
                                                        setForm({
                                                            ...form,
                                                            message:
                                                                e.target.value,
                                                        })
                                                    }
                                                    placeholder="Décrivez votre demande, question ou projet..."
                                                    className="w-full resize-none rounded-xl border border-border/50 bg-background/80 px-4 py-3 text-sm text-foreground transition-colors placeholder:text-foreground/30 focus:border-primary/50 focus:ring-1 focus:ring-primary/30 focus:outline-none"
                                                />
                                            </div>

                                            <Button
                                                type="submit"
                                                size="lg"
                                                className="w-full gap-2 rounded-full"
                                            >
                                                <Send className="h-4 w-4" />
                                                Envoyer le message
                                            </Button>
                                        </form>
                                    </>
                                ) : (
                                    <motion.div
                                        initial={{ opacity: 0, scale: 0.95 }}
                                        animate={{ opacity: 1, scale: 1 }}
                                        transition={{ duration: 0.4 }}
                                        className="flex flex-col items-center justify-center py-16 text-center"
                                    >
                                        <div className="mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                                            <CheckCircle2 className="h-8 w-8 text-primary" />
                                        </div>
                                        <h3 className="mb-3 text-xl font-semibold text-foreground">
                                            Message envoyé !
                                        </h3>
                                        <p className="text-foreground/55">
                                            Merci pour votre message. Notre
                                            équipe vous répondra dans les 24h.
                                        </p>
                                        <Button
                                            variant="outline"
                                            className="mt-8 rounded-full border-border/40"
                                            onClick={() => {
                                                setSent(false);
                                                setForm({
                                                    nom: '',
                                                    email: '',
                                                    objet: '',
                                                    message: '',
                                                });
                                            }}
                                        >
                                            Envoyer un autre message
                                        </Button>
                                    </motion.div>
                                )}
                            </CardContent>
                        </Card>
                    </motion.div>

                    {/* Infos pratiques */}
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true }}
                        variants={fadeUp}
                        className="space-y-5"
                    >
                        {/* Coordonnées */}
                        <Card className="border-border/40 bg-background/60 backdrop-blur-sm dark:border-border/50 dark:bg-background/50">
                            <CardContent className="p-7">
                                <h3 className="mb-6 text-sm font-semibold tracking-[0.2em] text-foreground/40 uppercase">
                                    Coordonnées
                                </h3>
                                <ul className="space-y-6">
                                    {infos.map((info) => {
                                        const Icon = info.icon;
                                        return (
                                            <li
                                                key={info.label}
                                                className="flex items-start gap-4"
                                            >
                                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/8">
                                                    <Icon className="h-4 w-4 text-primary/70" />
                                                </div>
                                                <div>
                                                    <p className="mb-0.5 text-xs font-semibold tracking-wider text-foreground/40 uppercase">
                                                        {info.label}
                                                    </p>
                                                    <p className="text-sm whitespace-pre-line text-foreground/70">
                                                        {info.value}
                                                    </p>
                                                </div>
                                            </li>
                                        );
                                    })}
                                </ul>
                            </CardContent>
                        </Card>

                        {/* Map placeholder */}
                        <Card className="overflow-hidden border-border/40 bg-background/60 backdrop-blur-sm dark:border-border/50 dark:bg-background/50">
                            <div className="relative h-48 overflow-hidden bg-muted/30">
                                <img
                                    src="https://images.unsplash.com/photo-1524661135-423995f22d0b?auto=format&fit=crop&w=900&q=80"
                                    alt="Localisation"
                                    className="h-full w-full object-cover opacity-60"
                                />
                                <div className="absolute inset-0 flex flex-col items-center justify-center gap-2">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg">
                                        <MapPin className="h-5 w-5" />
                                    </div>
                                    <span className="rounded-full bg-background/80 px-3 py-1 text-xs font-semibold text-foreground backdrop-blur">
                                        Paris · Communauté mondiale
                                    </span>
                                </div>
                            </div>
                        </Card>
                    </motion.div>
                </div>
            </div>
        </div>
    );
}
