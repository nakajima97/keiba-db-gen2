import BackButton from "@/components/presentational/BackButton";
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
import { index as racesIndex } from "@/routes/races";
import { useState } from "react";

type Props = {
	venues: { id: number; name: string }[];
	initialVenueId?: number;
	initialRaceDate?: string;
	initialRaceNumber?: number;
	initialRaceName?: string;
	onSubmit: (
		data: {
			venue_id: number;
			race_date: string;
			race_number: number;
			race_name: string | undefined;
			paste_text: string;
		},
		onSuccess: () => void,
	) => void;
};

const RACE_NUMBERS = Array.from({ length: 12 }, (_, i) => i + 1);

const RaceInputForm = ({
	venues,
	initialVenueId,
	initialRaceDate,
	initialRaceNumber,
	initialRaceName,
	onSubmit,
}: Props) => {
	const [venueId, setVenueId] = useState<string>(
		initialVenueId ? String(initialVenueId) : "",
	);
	const [raceDate, setRaceDate] = useState<string>(initialRaceDate ?? "");
	const [raceNumber, setRaceNumber] = useState<string>(
		initialRaceNumber ? String(initialRaceNumber) : "",
	);
	const [raceName, setRaceName] = useState<string>(initialRaceName ?? "");
	const [pasteText, setPasteText] = useState<string>("");

	const handleSubmit = () => {
		onSubmit(
			{
				venue_id: Number(venueId),
				race_date: raceDate,
				race_number: Number(raceNumber),
				race_name: raceName || undefined,
				paste_text: pasteText,
			},
			() => setPasteText(""),
		);
	};

	const isFormValid =
		venueId !== "" &&
		raceDate !== "" &&
		raceNumber !== "" &&
		pasteText.trim() !== "";

	return (
		<div className="flex flex-col gap-6 p-4">
			<div>
				<BackButton label="レース一覧へ戻る" href={racesIndex.url()} />
			</div>
			<div>
				<h1 className="text-xl font-semibold">レース情報入力</h1>
				<p className="text-sm text-muted-foreground">
					JRA公式サイトの出馬表をコピー＆ペーストしてレース情報を登録します。
				</p>
			</div>

			<div className="flex flex-col gap-4">
				<div className="flex flex-col gap-2">
					<Label htmlFor="venue">競馬場</Label>
					<Select value={venueId} onValueChange={setVenueId}>
						<SelectTrigger id="venue" className="w-full">
							<SelectValue placeholder="競馬場を選択" />
						</SelectTrigger>
						<SelectContent>
							{venues.map((venue) => (
								<SelectItem key={venue.id} value={String(venue.id)}>
									{venue.name}
								</SelectItem>
							))}
						</SelectContent>
					</Select>
				</div>

				<div className="flex flex-col gap-2">
					<Label htmlFor="race-date">レース日</Label>
					<Input
						id="race-date"
						type="date"
						value={raceDate}
						onChange={(e) => setRaceDate(e.target.value)}
					/>
				</div>

				<div className="flex flex-col gap-2">
					<Label htmlFor="race-number">レース番号</Label>
					<Select value={raceNumber} onValueChange={setRaceNumber}>
						<SelectTrigger id="race-number" className="w-full">
							<SelectValue placeholder="レース番号を選択" />
						</SelectTrigger>
						<SelectContent>
							{RACE_NUMBERS.map((n) => (
								<SelectItem key={n} value={String(n)}>
									{n}R
								</SelectItem>
							))}
						</SelectContent>
					</Select>
				</div>

				<div className="flex flex-col gap-2">
					<Label htmlFor="race-name">レース名</Label>
					<Input
						id="race-name"
						type="text"
						value={raceName}
						onChange={(e) => setRaceName(e.target.value)}
						placeholder="例：天皇賞（春）"
					/>
				</div>

				<div className="flex flex-col gap-2">
					<Label htmlFor="paste-text">出馬表をペースト</Label>
					<textarea
						id="paste-text"
						className="min-h-[240px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:border-ring"
						placeholder="JRA公式サイトの出馬表をコピー＆ペーストしてください"
						value={pasteText}
						onChange={(e) => setPasteText(e.target.value)}
					/>
				</div>
			</div>

			<Button onClick={handleSubmit} disabled={!isFormValid}>
				保存する
			</Button>
		</div>
	);
};

export default RaceInputForm;

export type { Props as RaceInputFormProps };
