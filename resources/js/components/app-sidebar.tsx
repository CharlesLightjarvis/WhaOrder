import { Link } from '@inertiajs/react';
import {
    BookOpen,
    FolderGit2,
    LayoutGrid,
    Package,
    Tag,
    Boxes,
    Users,
    MapPin,
    ShoppingCart,
    MessageCircle,
    Smartphone,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import AddressController from '@/actions/App/Http/Controllers/AddressController';
import OrderController from '@/actions/App/Http/Controllers/OrderController';
import ConversationController from '@/actions/App/Http/Controllers/ConversationController';
import WhatsAppSessionController from '@/actions/App/Http/Controllers/WhatsAppSessionController';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Catalogue',
        href: ProductController.index.url(),
        icon: Boxes,
        items: [
            {
                title: 'Produits',
                href: ProductController.index.url(),
                icon: Package,
            },
            {
                title: 'Catégories',
                href: CategoryController.index.url(),
                icon: Tag,
            },
        ],
    },
    {
        title: 'Clients',
        href: CustomerController.index.url(),
        icon: Users,
        items: [
            {
                title: 'Clients',
                href: CustomerController.index.url(),
                icon: Users,
            },
            {
                title: 'Adresses',
                href: AddressController.index.url(),
                icon: MapPin,
            },
        ],
    },
    {
        title: 'Commandes',
        href: OrderController.index.url(),
        icon: ShoppingCart,
    },
    {
        title: 'Conversations',
        href: ConversationController.index.url(),
        icon: MessageCircle,
    },
    {
        title: 'WhatsApp',
        href: WhatsAppSessionController.index.url(),
        icon: Smartphone,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
