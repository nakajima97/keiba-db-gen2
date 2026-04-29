import AppLayoutTemplate from "@/layouts/app/app-sidebar-layout";
import type { BreadcrumbItem } from "@/types";

const AppLayout = ({
	breadcrumbs = [],
	children,
}: {
	breadcrumbs?: BreadcrumbItem[];
	children: React.ReactNode;
}) => {
	return (
		<AppLayoutTemplate breadcrumbs={breadcrumbs}>{children}</AppLayoutTemplate>
	);
};

export default AppLayout;
