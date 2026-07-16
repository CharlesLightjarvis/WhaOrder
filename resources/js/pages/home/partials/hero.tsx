import { Link, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import type { Variants } from 'framer-motion';
import { MessageCircle } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';

type Point = {
    x: number;
    y: number;
};

interface WaveConfig {
    offset: number;
    amplitude: number;
    frequency: number;
    color: string;
    opacity: number;
}

const highlightPills = [
    'Directement dans WhatsApp',
    'Détection automatique des commandes',
    'Multi-commerçants',
] as const;

const heroStats: { label: string; value: string }[] = [
    { label: 'Commandes traitées', value: '12 000+' },
    { label: 'Commerçants actifs', value: '500+' },
    { label: 'Temps de traitement', value: '-70 %' },
];

const containerVariants: Variants = {
    hidden: { opacity: 0, y: 24 },
    visible: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.8, staggerChildren: 0.12 },
    },
};

const itemVariants: Variants = {
    hidden: { opacity: 0, y: 24 },
    visible: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.6, ease: 'easeOut' },
    },
};

const statsVariants: Variants = {
    hidden: { opacity: 0, scale: 0.95 },
    visible: {
        opacity: 1,
        scale: 1,
        transition: { duration: 0.6, ease: 'easeOut', staggerChildren: 0.08 },
    },
};

export function Hero() {
    const { auth } = usePage<{ auth: { user: { id: number } | null } }>().props;
    const submitHref = auth.user ? '/dashboard' : register();

    const canvasRef = useRef<HTMLCanvasElement | null>(null);
    const mouseRef = useRef<Point>({ x: 0, y: 0 });
    const targetMouseRef = useRef<Point>({ x: 0, y: 0 });

    useEffect(() => {
        const canvas = canvasRef.current;

        if (!canvas) {
            return undefined;
        }

        const ctx = canvas.getContext('2d');

        if (!ctx) {
            return undefined;
        }

        let animationId: number;
        let time = 0;

        const computeThemeColors = () => {
            const rootStyles = getComputedStyle(document.documentElement);

            const resolveColor = (variables: string[], alpha = 1) => {
                const tempEl = document.createElement('div');
                tempEl.style.position = 'absolute';
                tempEl.style.visibility = 'hidden';
                tempEl.style.width = '1px';
                tempEl.style.height = '1px';
                document.body.appendChild(tempEl);

                let color = `rgba(37, 211, 102, ${alpha})`;

                for (const variable of variables) {
                    const value = rootStyles.getPropertyValue(variable).trim();

                    if (value) {
                        tempEl.style.backgroundColor = `var(${variable})`;
                        const computedColor =
                            getComputedStyle(tempEl).backgroundColor;

                        if (
                            computedColor &&
                            computedColor !== 'rgba(0, 0, 0, 0)'
                        ) {
                            if (alpha < 1) {
                                const rgbMatch = computedColor.match(
                                    /rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*[\d.]+)?\)/,
                                );

                                if (rgbMatch) {
                                    color = `rgba(${rgbMatch[1]}, ${rgbMatch[2]}, ${rgbMatch[3]}, ${alpha})`;
                                } else {
                                    color = computedColor;
                                }
                            } else {
                                color = computedColor;
                            }

                            break;
                        }
                    }
                }

                document.body.removeChild(tempEl);

                return color;
            };

            return {
                wavePalette: [
                    {
                        offset: 0,
                        amplitude: 70,
                        frequency: 0.003,
                        color: resolveColor(['--primary'], 0.8),
                        opacity: 0.4,
                    },
                    {
                        offset: Math.PI / 2,
                        amplitude: 90,
                        frequency: 0.0026,
                        color: resolveColor(['--accent', '--primary'], 0.6),
                        opacity: 0.3,
                    },
                    {
                        offset: Math.PI,
                        amplitude: 60,
                        frequency: 0.0034,
                        color: resolveColor(
                            ['--secondary', '--foreground'],
                            0.5,
                        ),
                        opacity: 0.25,
                    },
                ] satisfies WaveConfig[],
            };
        };

        let themeColors = computeThemeColors();

        const handleThemeMutation = () => {
            themeColors = computeThemeColors();
        };

        const observer = new MutationObserver(handleThemeMutation);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class', 'data-theme'],
        });

        const prefersReducedMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        ).matches;

        const mouseInfluence = prefersReducedMotion ? 10 : 70;
        const influenceRadius = prefersReducedMotion ? 160 : 320;
        const smoothing = prefersReducedMotion ? 0.04 : 0.1;

        const resizeCanvas = () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        };

        const recenterMouse = () => {
            const centerPoint = { x: canvas.width / 2, y: canvas.height / 2 };
            mouseRef.current = centerPoint;
            targetMouseRef.current = centerPoint;
        };

        const handleResize = () => {
            resizeCanvas();
            recenterMouse();
        };

        const handleMouseMove = (event: MouseEvent) => {
            targetMouseRef.current = { x: event.clientX, y: event.clientY };
        };

        const handleMouseLeave = () => {
            recenterMouse();
        };

        resizeCanvas();
        recenterMouse();

        window.addEventListener('resize', handleResize);
        window.addEventListener('mousemove', handleMouseMove);
        window.addEventListener('mouseleave', handleMouseLeave);

        const drawWave = (wave: WaveConfig) => {
            ctx.save();
            ctx.beginPath();

            for (let x = 0; x <= canvas.width; x += 4) {
                const dx = x - mouseRef.current.x;
                const dy = canvas.height / 2 - mouseRef.current.y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                const influence = Math.max(0, 1 - distance / influenceRadius);
                const mouseEffect =
                    influence *
                    mouseInfluence *
                    Math.sin(time * 0.001 + x * 0.01 + wave.offset);

                const y =
                    canvas.height / 2 +
                    Math.sin(x * wave.frequency + time * 0.002 + wave.offset) *
                        wave.amplitude +
                    Math.sin(x * wave.frequency * 0.4 + time * 0.003) *
                        (wave.amplitude * 0.45) +
                    mouseEffect;

                if (x === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            }

            ctx.lineWidth = 2.5;
            ctx.strokeStyle = wave.color;
            ctx.globalAlpha = wave.opacity;
            ctx.shadowBlur = 35;
            ctx.shadowColor = wave.color;
            ctx.stroke();

            ctx.restore();
        };

        const animate = () => {
            time += 1;

            mouseRef.current.x +=
                (targetMouseRef.current.x - mouseRef.current.x) * smoothing;
            mouseRef.current.y +=
                (targetMouseRef.current.y - mouseRef.current.y) * smoothing;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            ctx.globalAlpha = 1;
            ctx.shadowBlur = 0;

            themeColors.wavePalette.forEach(drawWave);

            animationId = window.requestAnimationFrame(animate);
        };

        animationId = window.requestAnimationFrame(animate);

        return () => {
            window.removeEventListener('resize', handleResize);
            window.removeEventListener('mousemove', handleMouseMove);
            window.removeEventListener('mouseleave', handleMouseLeave);
            cancelAnimationFrame(animationId);
            observer.disconnect();
        };
    }, []);

    return (
        <section
            className="relative isolate flex min-h-screen w-full items-center overflow-hidden bg-background"
            role="region"
            aria-label="Présentation de WhaOrder, l'assistant qui transforme WhatsApp en desk de commandes"
        >
            {/* fond avec dégradés */}
            <div className="pointer-events-none absolute inset-0 z-0">
                <div className="absolute top-0 left-1/2 h-130 w-130 -translate-x-1/2 rounded-full bg-primary/5 blur-[140px] dark:bg-primary/8" />
                <div className="absolute right-0 bottom-0 h-90 w-90 rounded-full bg-foreground/2.5 blur-[120px] dark:bg-foreground/5" />
            </div>

            {/* canvas des vagues */}
            <canvas
                ref={canvasRef}
                className="absolute inset-0 z-10 h-full w-full"
                aria-hidden="true"
            />

            {/* contenu texte & CTA */}
            <div className="relative z-20 mx-auto w-full max-w-4xl px-6 py-24 text-center md:px-8 lg:px-12">
                <motion.div
                    variants={containerVariants}
                    initial="hidden"
                    animate="visible"
                >
                    <motion.div
                        variants={itemVariants}
                        className="mx-auto mb-6 inline-flex items-center gap-2 rounded-full border border-border bg-background px-4 py-2 text-xs font-semibold tracking-[0.15em] text-foreground/70 uppercase"
                    >
                        <MessageCircle
                            className="h-4 w-4 text-primary"
                            aria-hidden="true"
                        />
                        WhaOrder controle vos commandes WhatsApp
                    </motion.div>

                    <motion.h1
                        variants={itemVariants}
                        className="mx-auto mb-6 max-w-3xl text-4xl font-semibold tracking-tight text-foreground md:text-6xl lg:text-7xl"
                    >
                        Vos commandes WhatsApp,{' '}
                        <span className="text-primary">
                            enfin sous contrôle
                        </span>
                    </motion.h1>

                    <motion.p
                        variants={itemVariants}
                        className="mx-auto mb-10 max-w-2xl text-lg text-foreground/70 md:text-2xl"
                    >
                        WhaOrder transforme automatiquement vos conversations
                        WhatsApp en commandes...
                    </motion.p>

                    <motion.div
                        variants={itemVariants}
                        className="mb-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center"
                    >
                        <Button size="lg" className="group gap-2" asChild>
                            <Link href={submitHref}>Essayer gratuitement</Link>
                        </Button>
                    </motion.div>

                    <motion.ul
                        variants={itemVariants}
                        className="mb-12 flex flex-wrap items-center justify-center gap-3 text-xs tracking-widest text-foreground/70 uppercase dark:text-foreground/80"
                    >
                        {highlightPills.map((pill) => (
                            <li
                                key={pill}
                                className="rounded-full border border-border bg-background px-4 py-2"
                            >
                                {pill}
                            </li>
                        ))}
                    </motion.ul>

                    <motion.div
                        variants={statsVariants}
                        className="mx-auto grid max-w-2xl gap-4 rounded-2xl border border-border bg-background p-6 sm:grid-cols-3"
                    >
                        {heroStats.map((stat) => (
                            <motion.div
                                key={stat.label}
                                variants={itemVariants}
                                className="space-y-1 text-center"
                            >
                                <div className="text-xs tracking-[0.2em] text-foreground/50 uppercase dark:text-foreground/60">
                                    {stat.label}
                                </div>
                                <div className="text-3xl font-semibold text-foreground">
                                    {stat.value}
                                </div>
                            </motion.div>
                        ))}
                    </motion.div>
                </motion.div>
            </div>
        </section>
    );
}
