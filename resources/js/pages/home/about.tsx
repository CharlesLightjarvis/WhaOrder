import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Link } from '@inertiajs/react';
import { motion, type Variants } from 'framer-motion';
import {
    Heart,
    Sparkles,
    Lightbulb,
    GraduationCap,
    Users,
    ArrowRight,
    Globe,
} from 'lucide-react';

/* ── données ─────────────────────────────────────────────── */

const timeline = [
    {
        annee: '2021',
        titre: 'Naissance de pMindfull',
        description:
            "Fondée à Paris par une équipe de praticiens du bien-être et de passionnés du digital, pMindfull naît d'une conviction : rendre le mindfulness et les pratiques de bien-être accessibles à tous.",
    },
    {
        annee: '2022',
        titre: 'Premiers formateurs',
        description:
            'Sélection et intégration des 20 premiers formateurs certifiés. Lancement des premières formations en méditation, yoga et sophrologie. Plus de 500 étudiants dès la première année.',
    },
    {
        annee: '2023',
        titre: 'Croissance communautaire',
        description:
            'Franchissement du cap des 5 000 étudiants actifs. Ouverture du programme chakras et développement spirituel. Lancement des sessions live mensuelles avec les formateurs.',
    },
    {
        annee: '2024',
        titre: 'Expansion internationale',
        description:
            'Déploiement auprès de la communauté francophone mondiale. Plus de 200 formateurs certifiés, 500 cours disponibles et 10 000 étudiants transformés dans 30 pays.',
    },
];

const equipe = [
    {
        initiales: 'ML',
        nom: 'Marie Lefebvre',
        role: 'Co-fondatrice & Directrice bien-être',
        bio: 'Enseignante certifiée MBSR depuis 14 ans et psychologue clinicienne. Marie a accompagné plus de 2 000 personnes vers une vie plus sereine.',
    },
    {
        initiales: 'TD',
        nom: 'Thomas Durand',
        role: 'Co-fondateur & Directeur produit',
        bio: 'Entrepreneur digital et méditant zen depuis 10 ans. Thomas allie vision technologique et expérience spirituelle pour créer des outils d\'apprentissage transformateurs.',
    },
    {
        initiales: 'SC',
        nom: 'Sarah Chakroun',
        role: 'Responsable formateurs & Qualité',
        bio: 'Praticienne Ayurveda et coach certifiée ICF. Sarah sélectionne et accompagne nos formateurs pour garantir la qualité et l\'authenticité de chaque cours.',
    },
    {
        initiales: 'AB',
        nom: 'Antoine Bonneau',
        role: 'Responsable communauté',
        bio: 'Ancien sophrologue reconverti dans le numérique. Antoine anime notre communauté d\'apprenants et veille à ce que chacun trouve sa place sur pMindfull.',
    },
];

const valeurs = [
    {
        icon: Heart,
        titre: 'Bienveillance',
        description:
            "Chaque interaction sur pMindfull est guidée par la douceur et le respect. Nous croyons que la transformation durable naît dans un espace sûr et bienveillant.",
    },
    {
        icon: Sparkles,
        titre: 'Authenticité',
        description:
            'Nos formateurs sont des praticiens actifs et passionnés. Ce qu\'ils enseignent, ils le vivent. Pas de contenu superficiel — seulement des savoirs incarnés.',
    },
    {
        icon: Lightbulb,
        titre: 'Accessibilité',
        description:
            "Le bien-être ne devrait pas être un luxe. Nous concevons des formations claires, progressives et adaptées à tous les niveaux, pour rendre ces pratiques accessibles à chacun.",
    },
    {
        icon: GraduationCap,
        titre: 'Transformation',
        description:
            "Notre mission n'est pas de transmettre des informations, mais de catalyser de vraies transformations. Chaque cours est conçu pour produire des changements durables dans la vie réelle.",
    },
];

/* ── variants ────────────────────────────────────────────── */

const fadeUp: Variants = {
    hidden: { opacity: 0, y: 24 },
    visible: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.65, ease: 'easeOut' },
    },
};

const stagger: Variants = {
    hidden: {},
    visible: { transition: { staggerChildren: 0.1 } },
};

/* ── composant ───────────────────────────────────────────── */

export default function About() {
    return (
        <div className="relative min-h-screen">
            {/* ── Hero ───────────────────────────────────────────── */}
            <section className="relative overflow-hidden py-32 md:py-44">
                {/* background image */}
                <div className="absolute inset-0 z-0">
                    <img
                        src="https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=1800&q=80"
                        alt=""
                        aria-hidden="true"
                        className="h-full w-full object-cover"
                    />
                    <div className="absolute inset-0 bg-background/88 backdrop-blur-sm dark:bg-background/92" />
                </div>

                <div className="relative z-10 mx-auto max-w-4xl px-4 text-center md:px-6">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.6 }}
                    >
                        <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-border/40 bg-secondary px-4 py-2 text-xs font-semibold tracking-[0.25em] text-secondary-foreground uppercase backdrop-blur">
                            Qui sommes-nous
                        </div>
                        <h1 className="mb-6 text-4xl font-semibold tracking-tight text-foreground md:text-6xl">
                            Éveiller, former,{' '}
                            <span className="text-foreground/40">
                                transformer
                            </span>
                        </h1>
                        <p className="mx-auto max-w-2xl text-lg leading-relaxed text-foreground/60">
                            pMindfull est une plateforme dédiée au bien-être, à
                            la pleine conscience et au développement personnel —
                            où formateurs certifiés et apprenants motivés se
                            retrouvent pour une transformation durable.
                        </p>
                    </motion.div>

                    <motion.div
                        initial={{ opacity: 0, y: 16 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.5, delay: 0.25 }}
                        className="mt-10 flex flex-wrap items-center justify-center gap-4"
                    >
                        <div className="flex items-center gap-2 rounded-full border border-border/40 bg-background/60 px-5 py-2.5 text-sm backdrop-blur">
                            <GraduationCap className="h-4 w-4 text-primary/70" />
                            <span className="font-semibold text-foreground">
                                10 000+
                            </span>
                            <span className="text-foreground">
                                étudiants transformés
                            </span>
                        </div>
                        <div className="flex items-center gap-2 rounded-full border border-border/40 bg-background/60 px-5 py-2.5 text-sm backdrop-blur">
                            <Users className="h-4 w-4 text-primary/70" />
                            <span className="font-semibold text-foreground">
                                200+
                            </span>
                            <span className="text-foreground">
                                formateurs certifiés
                            </span>
                        </div>
                        <div className="flex items-center gap-2 rounded-full border border-border/40 bg-background/60 px-5 py-2.5 text-sm backdrop-blur">
                            <Globe className="h-4 w-4 text-primary/70" />
                            <span className="font-semibold text-foreground">
                                30+
                            </span>
                            <span className="text-foreground">
                                pays représentés
                            </span>
                        </div>
                    </motion.div>
                </div>
            </section>

            {/* ── Histoire / Timeline ────────────────────────────── */}
            <section className="relative py-24 md:py-32">
                <div className="pointer-events-none absolute inset-0 overflow-hidden">
                    <div className="absolute top-1/2 right-0 h-[500px] w-[500px] -translate-y-1/2 rounded-full bg-primary/[0.2] blur-[160px] dark:bg-primary/[0.06]" />
                </div>

                <div className="relative mx-auto max-w-5xl px-4 md:px-6">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true }}
                        variants={fadeUp}
                        className="mb-16 text-center"
                    >
                        <p className="mb-2 text-xs font-semibold tracking-[0.25em] text-foreground/40 uppercase">
                            Depuis 2021
                        </p>
                        <h2 className="text-3xl font-semibold tracking-tight text-foreground md:text-4xl">
                            Notre histoire
                        </h2>
                    </motion.div>

                    {/* timeline verticale */}
                    <div className="relative">
                        {/* ligne centrale desktop */}
                        <div className="absolute top-0 left-1/2 hidden h-full w-px -translate-x-1/2 bg-border lg:block" />

                        <div className="flex flex-col gap-12">
                            {timeline.map((item, i) => (
                                <motion.div
                                    key={item.annee}
                                    initial="hidden"
                                    whileInView="visible"
                                    viewport={{ once: true, margin: '-60px' }}
                                    variants={fadeUp}
                                    className={`relative flex flex-col gap-4 lg:flex-row lg:gap-0 ${
                                        i % 2 === 0
                                            ? 'lg:flex-row'
                                            : 'lg:flex-row-reverse'
                                    }`}
                                >
                                    {/* contenu */}
                                    <div
                                        className={`lg:w-[calc(50%-2rem)] ${i % 2 === 0 ? 'lg:pr-12 lg:text-right' : 'lg:pl-12'}`}
                                    >
                                        <Card className="border-border/40 bg-background/60 backdrop-blur-sm dark:border-border/50 dark:bg-background/50">
                                            <CardContent className="p-6">
                                                <p className="mb-1 text-xs font-bold tracking-[0.2em] text-primary/60 uppercase">
                                                    {item.annee}
                                                </p>
                                                <h3 className="mb-2 text-lg font-semibold text-foreground">
                                                    {item.titre}
                                                </h3>
                                                <p className="text-sm leading-relaxed text-foreground/60">
                                                    {item.description}
                                                </p>
                                            </CardContent>
                                        </Card>
                                    </div>

                                    {/* nœud central */}
                                    <div className="hidden lg:flex lg:w-16 lg:shrink-0 lg:items-center lg:justify-center">
                                        <div className="z-10 flex h-8 w-8 items-center justify-center rounded-full border-2 border-primary/40 bg-background text-xs font-bold text-primary/70">
                                            {String(i + 1).padStart(2, '0')}
                                        </div>
                                    </div>

                                    {/* espace vide côté opposé */}
                                    <div className="hidden lg:block lg:w-[calc(50%-2rem)]" />
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </div>
            </section>

            {/* ── Équipe ─────────────────────────────────────────── */}
            <section className="relative py-24 md:py-32">
                <div className="pointer-events-none absolute inset-0 overflow-hidden">
                    <div className="absolute top-1/2 left-0 h-[500px] w-[500px] -translate-y-1/2 rounded-full bg-foreground/[0.02] blur-[150px]" />
                </div>

                <div className="relative mx-auto max-w-7xl px-4 md:px-6">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true }}
                        variants={fadeUp}
                        className="mb-16 text-center"
                    >
                        <p className="mb-2 text-xs font-semibold tracking-[0.25em] text-foreground/40 uppercase">
                            Les personnes derrière la plateforme
                        </p>
                        <h2 className="text-3xl font-semibold tracking-tight text-foreground md:text-4xl">
                            Notre équipe
                        </h2>
                    </motion.div>

                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-80px' }}
                        variants={stagger}
                        className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        {equipe.map((membre) => (
                            <motion.div key={membre.nom} variants={fadeUp}>
                                <Card className="group h-full border-border/40 bg-background/60 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:border-border/60 hover:shadow-lg dark:border-border/50 dark:bg-background/50">
                                    <CardContent className="p-6">
                                        {/* avatar */}
                                        <div className="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-lg font-semibold text-primary">
                                            {membre.initiales}
                                        </div>
                                        <h3 className="mb-1 font-semibold text-foreground">
                                            {membre.nom}
                                        </h3>
                                        <p className="mb-4 text-xs font-medium text-primary/70">
                                            {membre.role}
                                        </p>
                                        <p className="text-sm leading-relaxed text-foreground/55">
                                            {membre.bio}
                                        </p>
                                    </CardContent>
                                </Card>
                            </motion.div>
                        ))}
                    </motion.div>
                </div>
            </section>

            {/* ── Valeurs ────────────────────────────────────────── */}
            <section className="relative py-24 md:py-32">
                <div className="pointer-events-none absolute inset-0 overflow-hidden">
                    <div className="absolute top-1/2 left-1/2 h-[600px] w-[600px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary/[0.03] blur-[180px] dark:bg-primary/[0.07]" />
                </div>

                <div className="relative mx-auto max-w-7xl px-4 md:px-6">
                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true }}
                        variants={fadeUp}
                        className="mb-16 text-center"
                    >
                        <p className="mb-2 text-xs font-semibold tracking-[0.25em] text-foreground/40 uppercase">
                            Ce qui nous guide
                        </p>
                        <h2 className="text-3xl font-semibold tracking-tight text-foreground md:text-4xl">
                            Nos valeurs
                        </h2>
                    </motion.div>

                    <motion.div
                        initial="hidden"
                        whileInView="visible"
                        viewport={{ once: true, margin: '-80px' }}
                        variants={stagger}
                        className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        {valeurs.map((val) => {
                            const Icon = val.icon;
                            return (
                                <motion.div key={val.titre} variants={fadeUp}>
                                    <Card className="h-full border-border/40 bg-background/60 backdrop-blur-sm dark:border-border/50 dark:bg-background/50">
                                        <CardContent className="p-8">
                                            <div className="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10">
                                                <Icon className="h-5 w-5 text-primary" />
                                            </div>
                                            <h3 className="mb-3 text-lg font-semibold text-foreground">
                                                {val.titre}
                                            </h3>
                                            <p className="text-sm leading-relaxed text-foreground/60">
                                                {val.description}
                                            </p>
                                        </CardContent>
                                    </Card>
                                </motion.div>
                            );
                        })}
                    </motion.div>
                </div>
            </section>

            {/* ── CTA ────────────────────────────────────────────── */}
            <section className="relative py-20 md:py-28">
                <motion.div
                    initial="hidden"
                    whileInView="visible"
                    viewport={{ once: true }}
                    variants={fadeUp}
                    className="mx-auto max-w-3xl px-4 text-center md:px-6"
                >
                    <h2 className="mb-5 text-3xl font-semibold tracking-tight text-foreground md:text-4xl">
                        Rejoignez l'aventure pMindfull
                    </h2>
                    <p className="mb-10 text-lg text-foreground/55">
                        Que vous souhaitiez apprendre, partager votre expertise
                        ou simplement en savoir plus, notre équipe est là pour
                        vous accompagner.
                    </p>
                    <div className="flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                        <Button
                            size="lg"
                            className="gap-2 rounded-full px-8"
                            asChild
                        >
                            <Link href="/contact">
                                Nous contacter
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button
                            size="lg"
                            variant="outline"
                            className="gap-2 rounded-full border-border/40 bg-background/60 px-8 backdrop-blur hover:bg-background/80"
                            asChild
                        >
                            <Link href="/courses">
                                Voir les formations
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                        </Button>
                    </div>
                </motion.div>
            </section>
        </div>
    );
}
