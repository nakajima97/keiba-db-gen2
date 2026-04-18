import { Link } from "@inertiajs/react";
import { BookOpen, Flag, FolderGit2, LayoutGrid } from "lucide-react";
import AppLogo from "@/components/presentational/AppLogo";
import { NavFooter } from "@/components/presentational/NavFooter";
import { NavMain } from "@/components/presentational/NavMain";
import { NavUser } from "@/components/presentational/NavUser";
import {
	Sidebar,
	SidebarContent,
	SidebarFooter,
	SidebarHeader,
	SidebarMenu,
	SidebarMenuButton,
	SidebarMenuItem,
} from "@/components/shadcn/ui/sidebar";
import { dashboard } from "@/routes";
import { index as racesIndex } from "@/routes/races";
import { index } from "@/routes/tickets";
import type { NavItem } from "@/types";

const mainNavItems: NavItem[] = [
	{
		title: "Dashboard",
		href: dashboard(),
		icon: LayoutGrid,
	},
	{
		title: "Tickets",
		href: index(),
		icon: FolderGit2,
	},
	{
		title: "Races",
		href: racesIndex(),
		icon: Flag,
	},
];

const footerNavItems: NavItem[] = [
	{
		title: "Repository",
		href: "https://github.com/laravel/react-starter-kit",
		icon: FolderGit2,
	},
	{
		title: "Documentation",
		href: "https://laravel.com/docs/starter-kits#react",
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
