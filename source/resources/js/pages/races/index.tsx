import { Head, Link } from "@inertiajs/react";
import { Button } from "@/components/shadcn/ui/button";
import { create } from "@/routes/races";

export default function RacesIndex() {
	return (
		<>
			<Head title="レース一覧" />
			<div className="flex flex-col gap-6 p-4">
				<div>
					<h1 className="text-xl font-semibold">レース一覧</h1>
				</div>
				<div>
					<Button asChild>
						<Link href={create()}>レース情報入力</Link>
					</Button>
				</div>
			</div>
		</>
	);
}
