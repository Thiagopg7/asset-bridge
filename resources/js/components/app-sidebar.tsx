import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    Building2,
    ClipboardList,
    FolderGit2,
    LayoutGrid,
    Package,
    ShieldCheck,
    Users,
} from 'lucide-react';
import AssetController from '@/actions/App/Http/Controllers/AssetController';
import AssetRequestController from '@/actions/App/Http/Controllers/AssetRequestController';
import BranchController from '@/actions/App/Http/Controllers/BranchController';
import RoleController from '@/actions/App/Http/Controllers/RoleController';
import UserController from '@/actions/App/Http/Controllers/UserController';
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
import type { NavItem } from '@/types';

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
    const { can } = usePage().props;

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        ...(can.viewBranches
            ? [
                  {
                      title: 'Filiais',
                      href: BranchController.index.url(),
                      icon: Building2,
                  },
              ]
            : []),
        ...(can.viewAssets
            ? [
                  {
                      title: 'Ativos',
                      href: AssetController.index.url(),
                      icon: Package,
                  },
              ]
            : []),
        ...(can.viewRequests
            ? [
                  {
                      title: 'Solicitações',
                      href: AssetRequestController.index.url(),
                      icon: ClipboardList,
                  },
              ]
            : []),
        ...(can.viewUsers
            ? [
                  {
                      title: 'Usuários',
                      href: UserController.index.url(),
                      icon: Users,
                  },
                  {
                      title: 'Cargos',
                      href: RoleController.index.url(),
                      icon: ShieldCheck,
                  },
              ]
            : []),
    ];

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
