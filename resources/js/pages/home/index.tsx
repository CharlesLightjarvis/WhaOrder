import { Head } from '@inertiajs/react';
import { Cta } from './partials/cta';
import FAQ from './partials/faq';
import { Features } from './partials/features';
import { Hero } from './partials/hero';
import { HowItWorks } from './partials/how-it-works';
import { Problem } from './partials/problem';

export default function Home() {
    return (
        <>
            <Head title="WhaOrder — Vos commandes WhatsApp sous contrôle" />
            <Hero />
            <Problem />
            <HowItWorks />
            <Features />
            <FAQ />
            <Cta />
        </>
    );
}
