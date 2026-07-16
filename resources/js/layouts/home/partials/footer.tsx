import {
    SiFacebook,
    SiInstagram,
    SiWhatsapp,
    SiX,
} from '@icons-pack/react-simple-icons';
import { motion, useReducedMotion } from 'framer-motion';
import { ArrowUp, Mail, MapPin, Phone } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

const footerLinks = [
    {
        title: 'Produit',
        links: [
            'Comment ça marche',
            'Fonctionnalités',
            'Tarifs',
            'Multi-commerçants',
        ],
    },
    {
        title: 'Ressources',
        links: ['Blog', 'Guide de démarrage', 'FAQ', 'Support'],
    },
    {
        title: 'Entreprise',
        links: ['À propos', 'Contact', 'Partenaires'],
    },
    {
        title: 'Légal',
        links: ['Confidentialité', 'CGU', 'Mentions légales'],
    },
];

const socialLinks = [
    { icon: SiWhatsapp, label: 'WhatsApp', href: '#' },
    { icon: SiX, label: 'Twitter', href: '#' },
    { icon: SiFacebook, label: 'Facebook', href: '#' },
    { icon: SiInstagram, label: 'Instagram', href: '#' },
];

export function Footer() {
    const scrollToTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const shouldReduceMotion = useReducedMotion();

    return (
        <footer
            aria-labelledby="footer-heading"
            className="relative w-full overflow-hidden border-t border-border bg-card"
        >
            <h2 id="footer-heading" className="sr-only">
                Site footer
            </h2>

            {/* Main Footer Content */}
            <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-6">
                    {/* Brand & Newsletter */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.5 }}
                        className="lg:col-span-2"
                    >
                        <motion.div
                            whileHover={
                                shouldReduceMotion ? undefined : { scale: 1.05 }
                            }
                            transition={{ duration: 0.2 }}
                            className="mb-4 inline-flex items-center gap-3"
                        >
                            <Card className="border border-border bg-card px-3 py-1 text-xs tracking-[0.32em] text-muted-foreground uppercase">
                                WhaOrder
                            </Card>
                            <Card className="border border-border bg-card px-3 py-1 text-xs tracking-[0.32em] text-muted-foreground uppercase">
                                Order Desk
                            </Card>
                        </motion.div>

                        <p className="mb-4 max-w-md text-sm text-muted-foreground">
                            WhaOrder transforme vos conversations WhatsApp en
                            commandes automatiquement : stock, prix,
                            livraison et paiement. Vous restez dans WhatsApp,
                            nous gérons le reste.
                        </p>

                        {/* Newsletter */}
                        <div className="mb-4">
                            <p className="mb-2 text-sm font-medium text-foreground">
                                Rejoins la newsletter WhaOrder
                            </p>
                            <div className="flex gap-2">
                                <Input
                                    type="email"
                                    placeholder="Votre adresse email"
                                />
                                <Button size="sm" aria-label="Subscribe">
                                    <Mail className="h-4 w-4" aria-hidden />
                                </Button>
                            </div>
                        </div>

                        {/* Contact Info */}
                        <div className="space-y-2 text-sm text-muted-foreground">
                            <motion.div
                                whileHover={
                                    shouldReduceMotion ? undefined : { x: 5 }
                                }
                                className="flex items-center gap-2"
                            >
                                <MapPin className="h-4 w-4" aria-hidden />
                                <span>Abidjan, Côte d'Ivoire — Afrique</span>
                            </motion.div>

                            <motion.div
                                whileHover={
                                    shouldReduceMotion ? undefined : { x: 5 }
                                }
                                className="flex items-center gap-2"
                            >
                                <Phone className="h-4 w-4" aria-hidden />
                                <span>+225 00 00 00 00 00</span>
                            </motion.div>

                            <motion.div
                                whileHover={
                                    shouldReduceMotion ? undefined : { x: 5 }
                                }
                                className="flex items-center gap-2"
                            >
                                <Mail className="h-4 w-4" aria-hidden />
                                <span>contact@whaorder.com</span>
                            </motion.div>
                        </div>
                    </motion.div>

                    {/* Footer Links - 2 colonnes sur mobile seulement */}
                    <div className="grid grid-cols-2 gap-8 md:contents">
                        {footerLinks.map((section, sectionIndex) => (
                            <motion.div
                                key={section.title}
                                initial={{ opacity: 0, y: 20 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true }}
                                transition={{
                                    duration: 0.5,
                                    delay: sectionIndex * 0.1,
                                }}
                            >
                                <h4 className="mb-4 text-sm font-semibold text-foreground/90">
                                    {section.title}
                                </h4>
                                <ul className="space-y-2">
                                    {section.links.map((link, linkIndex) => (
                                        <motion.li
                                            key={link}
                                            initial={{ opacity: 0, x: -10 }}
                                            whileInView={{ opacity: 1, x: 0 }}
                                            viewport={{ once: true }}
                                            transition={{
                                                delay: linkIndex * 0.05,
                                            }}
                                        >
                                            <motion.a
                                                href="#"
                                                whileHover={
                                                    shouldReduceMotion
                                                        ? undefined
                                                        : {
                                                              x: 5,
                                                              color: 'hsl(var(--primary))',
                                                          }
                                                }
                                                className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                                            >
                                                {link}
                                            </motion.a>
                                        </motion.li>
                                    ))}
                                </ul>
                            </motion.div>
                        ))}
                    </div>
                </div>

                {/* Divider */}
                <motion.div
                    initial={{ scaleX: 0 }}
                    whileInView={{ scaleX: 1 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.8, delay: 0.4 }}
                    className="my-10 h-px bg-border"
                />

                {/* Bottom Bar */}
                <div className="flex flex-col items-center justify-between gap-4 md:flex-row">
                    {/* Social Links */}
                    <motion.div
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        viewport={{ once: true }}
                        transition={{ delay: 0.5 }}
                        className="flex gap-2"
                    >
                        {socialLinks.map((social, index) => (
                            <motion.div
                                key={social.label}
                                initial={{ scale: 0 }}
                                whileInView={{ scale: 1 }}
                                viewport={{ once: true }}
                                transition={{
                                    type: 'spring',
                                    stiffness: 300,
                                    damping: 20,
                                    delay: 0.6 + index * 0.05,
                                }}
                            >
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    className="h-9 w-9 rounded-full border border-border/60 bg-white/5 text-muted-foreground transition-colors hover:bg-primary/10 hover:text-primary"
                                    aria-label={social.label}
                                >
                                    <motion.div
                                        transition={{
                                            duration: shouldReduceMotion
                                                ? 0.25
                                                : 0.3,
                                        }}
                                    >
                                        <social.icon
                                            className="h-4 w-4"
                                            aria-hidden
                                        />
                                    </motion.div>
                                </Button>
                            </motion.div>
                        ))}
                    </motion.div>

                    {/* Copyright */}
                    <motion.div
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        viewport={{ once: true }}
                        transition={{ delay: 0.6 }}
                        className="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <span>© 2026 WhaOrder. Tous droits réservés.</span>
                        <Badge variant="outline" className="text-xs">
                            v1.0.0
                        </Badge>
                    </motion.div>

                    {/* Scroll to Top */}
                    <motion.div
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        viewport={{ once: true }}
                        transition={{ delay: 0.7 }}
                    >
                        <Button
                            size="icon"
                            variant="outline"
                            className="h-9 w-9 rounded-full border-border/60"
                            onClick={scrollToTop}
                        >
                            <motion.div
                                animate={
                                    shouldReduceMotion
                                        ? undefined
                                        : { y: [0, -3, 0] }
                                }
                                transition={
                                    shouldReduceMotion
                                        ? undefined
                                        : { repeat: Infinity, duration: 1.5 }
                                }
                            >
                                <ArrowUp className="h-4 w-4" aria-hidden />
                            </motion.div>
                        </Button>
                    </motion.div>
                </div>
            </div>
        </footer>
    );
}
