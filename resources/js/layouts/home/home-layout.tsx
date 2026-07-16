import { Footer } from './partials/footer';
import { Header } from './partials/header';

export default function HomeLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return (
        <div className="flex min-h-svh flex-col bg-background text-foreground">
            <Header />

            <main className="flex-1">{children}</main>

            <Footer />
        </div>
    );
}
