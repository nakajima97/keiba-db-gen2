import { Head } from "@inertiajs/react";
import AppearanceTabs from "@/features/settings/presentational/AppearanceTabs";
import Heading from "@/components/presentational/Heading";
import { edit as editAppearance } from "@/routes/appearance";

export default function Appearance() {
	return (
		<>
			<Head title="Appearance settings" />

			<h1 className="sr-only">Appearance settings</h1>

			<div className="space-y-6">
				<Heading
					variant="small"
					title="Appearance settings"
					description="Update your account's appearance settings"
				/>
				<AppearanceTabs />
			</div>
		</>
	);
}

Appearance.layout = {
	breadcrumbs: [
		{
			title: "Appearance settings",
			href: editAppearance(),
		},
	],
};
