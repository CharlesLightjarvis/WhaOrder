import { Link, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { ArrowRight, MessageCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';

export function Cta() {
    const { auth } = usePage<{ auth: { user: { id: number } | null } }>().props;
    const submitHref = auth.user ? '/dashboard' : register();

    return (
        <section className="relative overflow-hidden py-20 md:py-28">
            <div className="pointer-events-none absolute inset-0">
                <div className="absolute top-0 left-1/2 h-90 w-90 -translate-x-1/2 rounded-full bg-white/10 blur-[130px]" />
            </div>

            <motion.div
                initial={{ opacity: 0, y: 24 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.7 }}
                className="relative mx-auto max-w-3xl px-6 text-center md:px-8"
            >
                <div className="mx-auto mb-6 inline-flex items-center gap-2 rounded-full border border-primary-foreground/30 bg-primary-foreground/10 px-4 py-2 text-xs font-semibold tracking-[0.15em] uppercase">
                    <MessageCircle className="h-4 w-4" aria-hidden="true" />
                    Prêt à commencer ?
                </div>

                <h2 className="mb-4 text-3xl font-semibold tracking-tight md:text-5xl">
                    Reprenez le contrôle de vos commandes WhatsApp
                </h2>

                <p className="mx-auto mb-10 max-w-xl text-lg opacity-90">
                    Créez votre espace commerçant et laissez WhaOrder
                    transformer vos conversations en commandes, dès aujourd'hui.
                </p>

                <Button
                    size="lg"
                    variant="secondary"
                    className="group gap-2"
                    asChild
                >
                    <Link href={submitHref}>
                        Essayer gratuitement
                        <ArrowRight
                            className="h-4 w-4 transition-transform group-hover:translate-x-1"
                            aria-hidden="true"
                        />
                    </Link>
                </Button>
            </motion.div>
        </section>
    );
}
