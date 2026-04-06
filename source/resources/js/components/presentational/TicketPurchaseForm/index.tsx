import { Button } from "@/components/shadcn/ui/button";
import { Input } from "@/components/shadcn/ui/input";
import { Label } from "@/components/shadcn/ui/label";
import { Badge } from "@/components/shadcn/ui/badge";
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from "@/components/shadcn/ui/select";

// ---------------------------------------------------------------------------
// 静的マスタデータ
// ---------------------------------------------------------------------------

export const VENUES = [
	"東京",
	"中山",
	"阪神",
	"京都",
	"新潟",
	"福島",
	"小倉",
	"函館",
	"札幌",
	"中京",
] as const;

export const TICKET_TYPES = [
	{ id: "tansho", label: "単勝" },
	{ id: "fukusho", label: "複勝" },
	{ id: "wakuren", label: "枠連" },
	{ id: "umaren", label: "馬連" },
	{ id: "umatan", label: "馬単" },
	{ id: "wide", label: "ワイド" },
	{ id: "sanrenpuku", label: "三連複" },
	{ id: "sanrentan", label: "三連単" },
] as const;

export type TicketTypeId = (typeof TICKET_TYPES)[number]["id"];

export const BUY_TYPE_MAP: Record<
	TicketTypeId,
	{ id: string; label: string }[]
> = {
	tansho: [{ id: "single", label: "通常" }],
	fukusho: [{ id: "single", label: "通常" }],
	wakuren: [
		{ id: "nagashi", label: "流し" },
		{ id: "box", label: "ボックス" },
	],
	umaren: [
		{ id: "nagashi", label: "流し" },
		{ id: "box", label: "ボックス" },
	],
	umatan: [
		{ id: "nagashi", label: "流し" },
		{ id: "box", label: "ボックス" },
		{ id: "formation", label: "フォーメーション" },
	],
	wide: [
		{ id: "nagashi", label: "流し" },
		{ id: "box", label: "ボックス" },
	],
	sanrenpuku: [
		{ id: "nagashi", label: "流し" },
		{ id: "box", label: "ボックス" },
	],
	sanrentan: [
		{ id: "nagashi", label: "流し" },
		{ id: "box", label: "ボックス" },
		{ id: "formation", label: "フォーメーション" },
	],
};

// 券種ごとのグリッドサイズ（枠連は枠番1〜8、それ以外は馬番1〜18）
const GRID_SIZE: Partial<Record<TicketTypeId, number>> = {
	wakuren: 8,
};

// 買い方 × 軸頭数ごとのグループ構成
const HORSE_INPUT_CONFIG: Record<string, { key: string; label: string }[]> = {
	single: [{ key: "horses", label: "馬番" }],
	box: [{ key: "horses", label: "馬番" }],
	nagashi_axis1: [
		{ key: "axis", label: "軸" },
		{ key: "others", label: "相手" },
	],
	nagashi_axis2: [
		{ key: "axis1", label: "軸1" },
		{ key: "axis2", label: "軸2" },
		{ key: "others", label: "相手" },
	],
	// 三連単流し・全券種フォーメーション（パターン④）
	formation: [
		{ key: "col1", label: "1着" },
		{ key: "col2", label: "2着" },
		{ key: "col3", label: "3着" },
	],
};

// ---------------------------------------------------------------------------
// 純粋関数: horseInputConfigKey の計算
// ---------------------------------------------------------------------------

export function getHorseInputConfigKey(
	ticketTypeId: TicketTypeId,
	buyTypeId: string,
	axisCount: 1 | 2,
	_nagashiDirection: 1 | 2 | 3,
): string {
	const showNagashiDirectionSelector =
		buyTypeId === "nagashi" && ticketTypeId === "sanrentan";

	if (buyTypeId === "nagashi") {
		return showNagashiDirectionSelector
			? "formation"
			: `nagashi_axis${axisCount}`;
	}
	return buyTypeId;
}

// ---------------------------------------------------------------------------
// Props
// ---------------------------------------------------------------------------

export type TicketPurchaseFormProps = {
	// レース情報
	selectedVenue: string;
	selectedRaceDate: string;
	selectedRaceNumber: number;
	// 券種・買い方
	selectedTicketTypeId: TicketTypeId;
	selectedBuyTypeId: string;
	// 軸頭数（三連複nagashiのみ有効）
	selectedAxisCount: 1 | 2;
	// 流し方向（三連単nagashiのみ有効）
	selectedNagashiDirection: 1 | 2 | 3;
	// 馬番選択
	selectedHorses: Record<string, number[]>;
	// 金額
	amount: number;
	// コールバック
	onVenueChange: (venue: string) => void;
	onRaceDateChange: (date: string) => void;
	onRaceNumberChange: (num: number) => void;
	onTicketTypeChange: (id: TicketTypeId) => void;
	onBuyTypeChange: (id: string) => void;
	onAxisCountChange: (count: 1 | 2) => void;
	onNagashiDirectionChange: (pos: 1 | 2 | 3) => void;
	onHorsesChange: (groupKey: string, horses: number[]) => void;
	onAmountChange: (amount: number) => void;
};

// ---------------------------------------------------------------------------
// サブコンポーネント
// ---------------------------------------------------------------------------

type SectionProps = {
	title: string;
	children: React.ReactNode;
};

function Section({ title, children }: SectionProps) {
	return (
		<section className="space-y-3">
			<h2 className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">
				{title}
			</h2>
			<div>{children}</div>
		</section>
	);
}

type HorseGridProps = {
	label: string;
	groupKey: string;
	gridSize: number;
	selectedHorses: number[];
	onToggle: (num: number) => void;
	onTextChange: (text: string) => void;
};

function HorseGrid({
	label,
	groupKey,
	gridSize,
	selectedHorses,
	onToggle,
	onTextChange,
}: HorseGridProps) {
	return (
		<div className="space-y-2">
			<div className="flex items-center gap-2">
				<Label className="text-sm font-medium">{label}</Label>
				<Input
					type="text"
					placeholder="例: 1 3 5 または 1,3,5"
					value={selectedHorses.join(", ")}
					className="h-8 w-48 text-sm"
					aria-label={`${label}の馬番テキスト入力`}
					data-group={groupKey}
					onChange={(e) => onTextChange(e.target.value)}
				/>
			</div>
			<div className="grid grid-cols-6 gap-1.5 sm:grid-cols-9">
				{Array.from({ length: gridSize }, (_, i) => i + 1).map((num) => {
					const isSelected = selectedHorses.includes(num);
					return (
						<button
							key={num}
							type="button"
							aria-pressed={isSelected}
							aria-label={`${num}番`}
							onClick={() => onToggle(num)}
							className={[
								"flex h-10 w-10 items-center justify-center rounded-lg border text-sm font-bold transition-colors",
								isSelected
									? "border-primary bg-primary text-primary-foreground"
									: "border-border bg-background text-foreground hover:bg-accent",
							].join(" ")}
						>
							{num}
						</button>
					);
				})}
			</div>
		</div>
	);
}

// ---------------------------------------------------------------------------
// メインコンポーネント
// ---------------------------------------------------------------------------

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
			{/* ----------------------------------------------------------------
			    1. レース情報
			---------------------------------------------------------------- */}
			<Section title="レース情報">
				<div className="space-y-4">
					{/* 開催場所 */}
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

					{/* 開催日 */}
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

					{/* レース番号 */}
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

			{/* ----------------------------------------------------------------
			    2. 券種選択
			---------------------------------------------------------------- */}
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

			{/* ----------------------------------------------------------------
			    3. 買い方選択
			---------------------------------------------------------------- */}
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

			{/* ----------------------------------------------------------------
			    4. 馬番入力
			---------------------------------------------------------------- */}
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

			{/* ----------------------------------------------------------------
			    5. 金額入力
			---------------------------------------------------------------- */}
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
							<Badge
								key={preset}
								variant="secondary"
								className="cursor-pointer select-none px-2 py-1 text-xs"
								onClick={() => onAmountChange(preset)}
							>
								{preset.toLocaleString()}円
							</Badge>
						))}
					</div>
				</div>
			</Section>

			{/* ----------------------------------------------------------------
			    6. 登録ボタン
			---------------------------------------------------------------- */}
			<div className="flex gap-3 pt-2">
				<Button type="submit" className="flex-1">
					登録する
				</Button>
				<Button type="button" variant="outline">
					キャンセル
				</Button>
			</div>
		</div>
	);
}
