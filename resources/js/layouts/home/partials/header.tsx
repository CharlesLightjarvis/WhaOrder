import { Link, usePage } from '@inertiajs/react';
import React from 'react';
import { createPortal } from 'react-dom';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { dashboard, login, register } from '@/routes';
import type { Auth } from '@/types';
import Logo from './logo';
import { MenuToggleIcon } from './menu-toggle-icon';
import ThemeToggle from './ThemeToggle';
import { useScroll } from './use-scroll';

/* ------------------------------------------------------------------ */
/* Header                                                             */
/* ------------------------------------------------------------------ */

export function Header() {
    const [open, setOpen] = React.useState(false);
    const scrolled = useScroll(10);
    const { auth } = usePage<{ auth: Auth }>().props;

    React.useEffect(() => {
        document.body.style.overflow = open ? 'hidden' : '';

        return () => {
            document.body.style.overflow = '';
        };
    }, [open]);

    return (
        <header
            className={cn(
                'sticky top-0 z-50 w-full border-b border-transparent',
                {
                    'border-border bg-background': scrolled,
                },
            )}
        >
            <nav className="relative flex h-14 w-full items-center px-4 lg:px-6">
                {/* logo */}
                <div className="flex items-center justify-start">
                    <Link href="/">
                        <Logo className="h-8 w-auto" />
                    </Link>
                </div>

                {/* CTA — droite */}
                <div className="ml-auto hidden items-center justify-end gap-2 lg:flex">
                    {auth.user ? (
                        <Button variant="secondary" asChild>
                            <Link href={dashboard()}>Dashboard</Link>
                        </Button>
                    ) : (
                        <>
                            <Button variant="secondary" asChild>
                                <Link href={login()}>Connexion</Link>
                            </Button>
                            <Button variant="default" asChild>
                                <Link href={register()}>S'inscrire</Link>
                            </Button>
                        </>
                    )}
                    <ThemeToggle />
                </div>

                {/* burger mobile */}
                <Button
                    size="icon"
                    variant="outline"
                    onClick={() => setOpen(!open)}
                    className="ml-auto lg:hidden"
                    aria-expanded={open}
                    aria-controls="mobile-menu"
                    aria-label="Toggle menu"
                >
                    <MenuToggleIcon
                        open={open}
                        className="size-5"
                        duration={300}
                    />
                </Button>
            </nav>

            <MobileMenu open={open} auth={auth} />
        </header>
    );
}

/* ------------------------------------------------------------------ */
/* Menu mobile                                                        */
/* ------------------------------------------------------------------ */

function MobileMenu({ open, auth }: { open: boolean; auth: Auth }) {
    if (!open || typeof window === 'undefined') {
        return null;
    }

    return createPortal(
        <div
            id="mobile-menu"
            className="fixed top-14 right-0 bottom-0 left-0 z-40 flex flex-col overflow-hidden border-y border-border bg-background lg:hidden"
        >
            <div
                data-slot={open ? 'open' : 'closed'}
                className="flex size-full flex-col justify-between overflow-y-auto p-4 ease-out data-[slot=open]:animate-in data-[slot=open]:zoom-in-97"
            >
                <div className="flex flex-col gap-3 pt-4">
                    <div className="flex items-center justify-between px-1">
                        <span className="text-xs font-medium text-foreground/40">
                            Apparence
                        </span>
                        <ThemeToggle />
                    </div>

                    {auth.user ? (
                        <Button variant="secondary" className="w-full" asChild>
                            <Link href={dashboard()}>Dashboard</Link>
                        </Button>
                    ) : (
                        <>
                            <Button
                                variant="secondary"
                                className="w-full"
                                asChild
                            >
                                <Link href={login()}>Connexion</Link>
                            </Button>
                            <Button
                                variant="default"
                                className="w-full"
                                asChild
                            >
                                <Link href={register()}>S'inscrire</Link>
                            </Button>
                        </>
                    )}
                </div>
            </div>
        </div>,
        document.body,
    );
}
