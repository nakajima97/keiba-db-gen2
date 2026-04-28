import type * as React from "react";
import { SidebarInset } from "@/components/shadcn/ui/sidebar";
import type { AppVariant } from "@/types";

type Props = React.ComponentProps<"main"> & {
	variant?: AppVariant;
};

export const AppContent = ({
	variant = "sidebar",
	children,
	...props
}: Props) => {
	if (variant === "sidebar") {
		return <SidebarInset {...props}>{children}</SidebarInset>;
	}

	return (
		<main
			className="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl"
			{...props}
		>
			{children}
		</main>
	);
};
