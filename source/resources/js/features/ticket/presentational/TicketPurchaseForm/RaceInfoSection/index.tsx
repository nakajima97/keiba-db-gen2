import { Button } from "@/components/shadcn/ui/button";
import { Input } from "@/components/shadcn/ui/input";
import { Label } from "@/components/shadcn/ui/label";
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from "@/components/shadcn/ui/select";
import { VENUES } from "../constants";
import { Section } from "../Section";
import type { RaceInfoSectionProps } from "./types";

export const RaceInfoSection = ({
	selectedVenue,
	selectedRaceDate,
	selectedRaceNumber,
	onVenueChange,
	onRaceDateChange,
	onRaceNumberChange,
}: RaceInfoSectionProps) => {
	return (
		<Section title="レース情報">
			<div className="space-y-4">
				<div className="space-y-2">
					<Label htmlFor="venue">開催場所</Label>
					<Select value={selectedVenue} onValueChange={onVenueChange}>
						<SelectTrigger id="venue" className="w-40">
							<SelectValue placeholder="選択してください" />
						</SelectTrigger>
						<SelectContent>
							{VENUES.map((venue) => (
								<SelectItem key={venue} value={venue}>
									{venue}
								</SelectItem>
							))}
						</SelectContent>
					</Select>
				</div>

				<div className="space-y-2">
					<Label htmlFor="race-date">開催日</Label>
					<Input
						id="race-date"
						type="date"
						value={selectedRaceDate}
						className="w-48"
						onChange={(e) => onRaceDateChange(e.target.value)}
					/>
				</div>

				<div className="space-y-2">
					<Label htmlFor="race-number">レース番号</Label>
					<div className="space-y-2">
						<Input
							id="race-number"
							type="number"
							min={1}
							max={12}
							value={selectedRaceNumber}
							className="h-8 w-16 text-center"
							aria-label="レース番号を直接入力"
							onChange={(e) => {
								const n = Number.parseInt(e.target.value, 10);
								if (n >= 1 && n <= 12) onRaceNumberChange(n);
							}}
						/>
						<div className="flex flex-wrap gap-1.5">
							{Array.from({ length: 12 }, (_, i) => i + 1).map((r) => (
								<Button
									key={r}
									type="button"
									variant={r === selectedRaceNumber ? "default" : "outline"}
									size="sm"
									aria-pressed={r === selectedRaceNumber}
									className="w-10"
									onClick={() => onRaceNumberChange(r)}
								>
									{r}R
								</Button>
							))}
						</div>
					</div>
				</div>
			</div>
		</Section>
	);
};
