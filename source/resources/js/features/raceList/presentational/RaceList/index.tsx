import { Link } from "@inertiajs/react";
import { Button } from "@/components/shadcn/ui/button";
import { Input } from "@/components/shadcn/ui/input";
import { Label } from "@/components/shadcn/ui/label";
import { create } from "@/routes/races";
import type { RaceListProps } from "./types";

export default function RaceList({
	races,
	venues,
	selectedDate,
	selectedVenueId,
	onDateChange,
	onVenueChange,
}: RaceListProps) {
	return (
		<div className="flex flex-col gap-4 p-4">
			<div className="flex items-center justify-between">
				<h1 className="text-xl font-semibold">レース一覧</h1>
				<Link href={create.url()}>レース情報入力</Link>
			</div>

			<div className="flex flex-wrap gap-4">
				<div className="flex flex-col gap-1.5">
					<Label htmlFor="race-date-filter">日付</Label>
					<Input
						id="race-date-filter"
						type="date"
						value={selectedDate}
						onChange={(e) => onDateChange(e.target.value)}
						className="w-40"
					/>
				</div>
				<div className="flex flex-col gap-1.5">
					<Label>開催場所</Label>
					<div className="flex flex-wrap gap-1.5">
						<Button
							variant={selectedVenueId === "all" ? "default" : "outline"}
							size="sm"
							onClick={() => onVenueChange("all")}
						>
							すべて
						</Button>
						{venues.map((venue) => (
							<Button
								key={venue.id}
								variant={selectedVenueId === String(venue.id) ? "default" : "outline"}
								size="sm"
								onClick={() => onVenueChange(String(venue.id))}
							>
								{venue.name}
							</Button>
						))}
					</div>
				</div>
			</div>

			{races.length === 0 ? (
				<div className="flex flex-col items-center justify-center gap-4 py-16 text-muted-foreground">
					<p>レースが見つかりません</p>
					<Link href={create.url()}>レース情報入力</Link>
				</div>
			) : (
				<div className="overflow-x-auto rounded-xl border">
					<table className="w-full text-sm">
						<thead>
							<tr className="border-b bg-muted/50">
								<th className="px-4 py-3 text-left font-medium text-muted-foreground">
									日付
								</th>
								<th className="px-4 py-3 text-left font-medium text-muted-foreground">
									開催場所
								</th>
								<th className="px-4 py-3 text-left font-medium text-muted-foreground">
									レース番号
								</th>
							</tr>
						</thead>
						<tbody>
							{races.map((race) => (
								<tr
									key={race.uid}
									className="cursor-pointer border-b last:border-0 hover:bg-muted/30"
								>
									<td className="px-4 py-3">
										{race.race_date.replace(/-/g, "/")}
									</td>
									<td className="px-4 py-3">{race.venue_name}</td>
									<td className="px-4 py-3">{race.race_number}R</td>
								</tr>
							))}
						</tbody>
					</table>
				</div>
			)}
		</div>
	);
}

export type { RaceListItem, RaceListProps } from "./types";
