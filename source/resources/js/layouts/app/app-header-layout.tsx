import { AppContent } from "@/components/presentational/AppContent";
import { AppHeader } from "@/components/presentational/AppHeader";
import { AppShell } from "@/components/presentational/AppShell";
import type { AppLayoutProps } from "@/types";

const AppHeaderLayout = ({ children, breadcrumbs }: AppLayoutProps) => {
	return (
		<AppShell variant="header">
			<AppHeader breadcrumbs={breadcrumbs} />
			<AppContent variant="header">{children}</AppContent>
		</AppShell>
	);
};

export default AppHeaderLayout;
