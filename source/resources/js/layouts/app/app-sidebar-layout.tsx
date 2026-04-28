import { AppContent } from "@/components/presentational/AppContent";
import { AppShell } from "@/components/presentational/AppShell";
import { AppSidebar } from "@/components/presentational/AppSidebar";
import { AppSidebarHeader } from "@/components/presentational/AppSidebarHeader";
import type { AppLayoutProps } from "@/types";

const AppSidebarLayout = ({
	children,
	breadcrumbs = [],
}: AppLayoutProps) => {
	return (
		<AppShell variant="sidebar">
			<AppSidebar />
			<AppContent variant="sidebar" className="overflow-x-hidden">
				<AppSidebarHeader breadcrumbs={breadcrumbs} />
				{children}
			</AppContent>
		</AppShell>
	);
};

export default AppSidebarLayout;
