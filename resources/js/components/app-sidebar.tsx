import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { ArrowLeftRight, Landmark, LayoutGrid, Lightbulb, Target, Wallet, Zap } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        url: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Bank Accounts',
        url: '/bank-connections',
        icon: Landmark,
    },
    {
        title: 'Accounts',
        url: '/accounts',
        icon: Wallet,
    },
    {
        title: 'Transactions',
        url: '/transactions',
        icon: ArrowLeftRight,
    },
    {
        title: 'Goals',
        url: '/goals',
        icon: Target,
    },
    {
        title: 'Rules',
        url: '/rules',
        icon: Zap,
    },
    {
        title: 'Insights',
        url: '/insights',
        icon: Lightbulb,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
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
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
