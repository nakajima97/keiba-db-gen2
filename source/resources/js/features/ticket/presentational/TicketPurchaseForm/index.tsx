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
import {
	BUY_TYPE_MAP,
	GRID_SIZE,
	HORSE_INPUT_CONFIG,
	TICKET_TYPES,
	VENUES,
} from "./constants";
import { HorseGrid } from "./HorseGrid";
import { Section } from "./Section";
import type { TicketPurchaseFormProps } from "./types";
import { getHorseInputConfigKey } from "./utils";

// テストファイルおよびStorybookから./indexを参照しているため、互換性のためにre-exportする
export { TICKET_TYPES, BUY_TYPE_MAP } from "./constants";
export type { TicketTypeId } from "./constants";
export { getHorseInputConfigKey } from "./utils";
export type { TicketPurchaseFormProps } from "./types";

export default function TicketPurchaseForm({
	selectedVenue,
	selectedRaceDate,
	selectedRaceNumber,
	selectedTicketTypeId,
	selectedBuyTypeId,
	selectedAxisCount,
	selectedNagashiDirection,
	selectedHorses,
	amount,
	processing,
	onVenueChange,
	onRaceDateChange,
	onRaceNumberChange,
	onTicketTypeChange,
	onBuyTypeChange,
	onAxisCountChange,
	onNagashiDirectionChange,
	onHorsesChange,
	onAmountChange,
}: TicketPurchaseFormProps) {
	const buyTypes = BUY_TYPE_MAP[selectedTicketTypeId];
	const gridSize = GRID_SIZE[selectedTicketTypeId] ?? 18;

	// 三連複nagashiのみ軸頭数を選択できる
	const showAxisCountSelector =
		selectedBuyTypeId === "nagashi" && selectedTicketTypeId === "sanrenpuku";

	// 三連単nagashiのみ流し方向を選択できる
	const showNagashiDirectionSelector =
		selectedBuyTypeId === "nagashi" && selectedTicketTypeId === "sanrentan";

	const horseInputConfigKey = getHorseInputConfigKey(
		selectedTicketTypeId,
		selectedBuyTypeId,
		selectedAxisCount,
		selectedNagashiDirection,
	);

	const horseGroups = HORSE_INPUT_CONFIG[horseInputConfigKey] ?? [];

	const handleHorseToggle = (groupKey: string, num: number) => {
		const current = selectedHorses[groupKey] ?? [];
		const next = current.includes(num)
			? current.filter((n) => n !== num)
			: [...current, num];
		onHorsesChange(groupKey, next);
	};

	const handleHorseTextChange = (groupKey: string, text: string) => {
		const nums = text
			.split(/[\s,]+/)
			.map(Number)
			.filter((n) => Number.isInteger(n) && n >= 1 && n <= gridSize);
		onHorsesChange(groupKey, nums);
	};

	return (
		<div className="mx-auto max-w-2xl space-y-8 p-4">
			{/* 1. レース情報 */}
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

			{/* 2. 券種選択 */}
			<Section title="券種">
				<div className="flex flex-wrap gap-2">
					{TICKET_TYPES.map(({ id, label }) => (
						<Button
							key={id}
							type="button"
							variant={id === selectedTicketTypeId ? "default" : "outline"}
							size="sm"
							aria-pressed={id === selectedTicketTypeId}
							onClick={() => onTicketTypeChange(id)}
						>
							{label}
						</Button>
					))}
				</div>
			</Section>

			{/* 3. 買い方選択 */}
			<Section title="買い方">
				<div className="flex flex-wrap gap-2">
					{buyTypes.map(({ id, label }) => (
						<Button
							key={id}
							type="button"
							variant={id === selectedBuyTypeId ? "default" : "outline"}
							size="sm"
							aria-pressed={id === selectedBuyTypeId}
							onClick={() => onBuyTypeChange(id)}
						>
							{label}
						</Button>
					))}
				</div>
			</Section>

			{/* 4. 馬番入力 */}
			<Section title="馬番">
				<div className="space-y-6">
					{/* 三連複nagashiのみ：軸頭数セレクター */}
					{showAxisCountSelector && (
						<div className="space-y-2">
							<Label>軸の頭数</Label>
							<div className="flex gap-2">
								{([1, 2] as const).map((count) => (
									<Button
										key={count}
										type="button"
										variant={
											count === selectedAxisCount ? "default" : "outline"
										}
										size="sm"
										aria-pressed={count === selectedAxisCount}
										onClick={() => onAxisCountChange(count)}
									>
										{count}頭軸
									</Button>
								))}
							</div>
						</div>
					)}
					{/* 三連単nagashiのみ：流し方向セレクター */}
					{showNagashiDirectionSelector && (
						<div className="space-y-2">
							<Label>流し方向</Label>
							<div className="flex gap-2">
								{([1, 2, 3] as const).map((pos) => (
									<Button
										key={pos}
										type="button"
										variant={
											pos === selectedNagashiDirection ? "default" : "outline"
										}
										size="sm"
										aria-pressed={pos === selectedNagashiDirection}
										onClick={() => onNagashiDirectionChange(pos)}
									>
										{pos}着流し
									</Button>
								))}
							</div>
						</div>
					)}
					{horseGroups.map(({ key, label }) => (
						<HorseGrid
							key={key}
							groupKey={key}
							label={label}
							gridSize={gridSize}
							selectedHorses={selectedHorses[key] ?? []}
							onToggle={(num) => handleHorseToggle(key, num)}
							onTextChange={(text) => handleHorseTextChange(key, text)}
						/>
					))}
				</div>
			</Section>

			{/* 5. 金額入力 */}
			<Section title="金額">
				<div className="flex items-center gap-2">
					<Button
						type="button"
						variant="outline"
						size="icon"
						aria-label="100円減らす"
						onClick={() => onAmountChange(Math.max(100, amount - 100))}
					>
						−
					</Button>
					<Input
						type="number"
						min={100}
						step={100}
						value={amount}
						className="w-28 text-center"
						aria-label="購入金額（円）"
						onChange={(e) => {
							const n = Number.parseInt(e.target.value, 10);
							if (n >= 100) onAmountChange(n);
						}}
					/>
					<span className="text-sm text-muted-foreground">円</span>
					<Button
						type="button"
						variant="outline"
						size="icon"
						aria-label="100円増やす"
						onClick={() => onAmountChange(amount + 100)}
					>
						＋
					</Button>
					<div className="ml-4 flex gap-1.5">
						{[100, 500, 1000].map((preset) => (
							<Button
								key={preset}
								type="button"
								variant="secondary"
								size="sm"
								onClick={() => onAmountChange(preset)}
							>
								{preset.toLocaleString()}円
							</Button>
						))}
					</div>
				</div>
			</Section>

			{/* 6. 登録ボタン */}
			<div className="flex gap-3 pt-2">
				<Button type="submit" className="flex-1" disabled={processing}>
					{processing ? "送信中..." : "登録する"}
				</Button>
				<Button type="button" variant="outline">
					キャンセル
				</Button>
			</div>
		</div>
	);
}
