import { Head } from "@inertiajs/react";
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
// 静的データ（UI構造確認用）
// ---------------------------------------------------------------------------

const VENUES = [
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

type Venue = (typeof VENUES)[number];

const TICKET_TYPES = [
	{ id: "tansho", label: "単勝" },
	{ id: "fukusho", label: "複勝" },
	{ id: "wakuren", label: "枠連" },
	{ id: "umaren", label: "馬連" },
	{ id: "umatan", label: "馬単" },
	{ id: "wide", label: "ワイド" },
	{ id: "sanrenpuku", label: "三連複" },
	{ id: "sanrentan", label: "三連単" },
] as const;

type TicketTypeId = (typeof TICKET_TYPES)[number]["id"];

// 券種ごとに選択可能な買い方
const BUY_TYPE_MAP: Record<TicketTypeId, { id: string; label: string }[]> = {
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

// 買い方ごとの馬番入力ラベル設定
type HorseInputConfig = {
	// 入力グループのラベルと対応するキー
	groups: { key: string; label: string }[];
};

const HORSE_INPUT_CONFIG: Record<string, HorseInputConfig> = {
	// ①複数頭選択のみ（single / box）
	single: { groups: [{ key: "horses", label: "馬番" }] },
	box: { groups: [{ key: "horses", label: "馬番" }] },
	// ②軸1頭+相手複数（nagashi — デフォルト表示は1軸）
	nagashi: {
		groups: [
			{ key: "axis", label: "軸" },
			{ key: "others", label: "相手" },
		],
	},
	// ④着順別に複数頭選択（formation）
	formation: {
		groups: [
			{ key: "col1", label: "1着" },
			{ key: "col2", label: "2着" },
			{ key: "col3", label: "3着" },
		],
	},
};

// 静的な表示用デフォルト選択値
const STATIC_SELECTED_VENUE: Venue = "東京";
const STATIC_SELECTED_TICKET: TicketTypeId = "umaren";
const STATIC_SELECTED_BUY_TYPE = "nagashi";
const STATIC_SELECTED_RACE_NUMBER = 1;
// 各グループで選択済みの馬番（表示確認用）
const STATIC_SELECTED_HORSES: Record<string, number[]> = {
	axis: [3],
	others: [1, 5, 7],
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
	selectedHorses: number[];
	groupKey: string;
};

function HorseGrid({ label, selectedHorses, groupKey }: HorseGridProps) {
	return (
		<div className="space-y-2">
			<div className="flex items-center gap-2">
				<Label className="text-sm font-medium">{label}</Label>
				<Input
					type="text"
					placeholder="例: 1 3 5 または 1,3,5"
					defaultValue={selectedHorses.join(", ")}
					className="h-8 w-48 text-sm"
					aria-label={`${label}の馬番テキスト入力`}
					data-group={groupKey}
				/>
			</div>
			<div className="grid grid-cols-6 gap-1.5 sm:grid-cols-9">
				{Array.from({ length: 18 }, (_, i) => i + 1).map((num) => {
					const isSelected = selectedHorses.includes(num);
					return (
						<button
							key={num}
							type="button"
							aria-pressed={isSelected}
							aria-label={`${num}番`}
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
// ページコンポーネント
// ---------------------------------------------------------------------------

export default function TicketsNew() {
	// デフォルト表示用の静的値
	const selectedTicket = STATIC_SELECTED_TICKET;
	const selectedBuyType = STATIC_SELECTED_BUY_TYPE;
	const buyTypes = BUY_TYPE_MAP[selectedTicket];
	const horseInputConfig = HORSE_INPUT_CONFIG[selectedBuyType];

	return (
		<>
			<Head title="馬券登録" />

			<div className="mx-auto max-w-2xl space-y-8 p-4">
				{/* ----------------------------------------------------------------
				    1. レース情報
				---------------------------------------------------------------- */}
				<Section title="レース情報">
					<div className="space-y-4">
						{/* 開催場所 */}
						<div className="space-y-2">
							<Label htmlFor="venue">開催場所</Label>
							<Select defaultValue={STATIC_SELECTED_VENUE}>
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
								defaultValue="2026-04-05"
								className="w-48"
							/>
						</div>

						{/* R番号 */}
						<div className="space-y-2">
							<Label>R番号</Label>
							<div className="flex flex-wrap items-center gap-2">
								<div className="flex flex-wrap gap-1.5">
									{Array.from({ length: 12 }, (_, i) => i + 1).map((r) => (
										<Button
											key={r}
											type="button"
											variant={
												r === STATIC_SELECTED_RACE_NUMBER
													? "default"
													: "outline"
											}
											size="sm"
											aria-pressed={r === STATIC_SELECTED_RACE_NUMBER}
											className="w-10"
										>
											{r}R
										</Button>
									))}
								</div>
								<Input
									type="number"
									min={1}
									max={12}
									defaultValue={STATIC_SELECTED_RACE_NUMBER}
									className="h-8 w-16 text-center"
									aria-label="R番号を直接入力"
								/>
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
								variant={id === selectedTicket ? "default" : "outline"}
								size="sm"
								aria-pressed={id === selectedTicket}
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
								variant={id === selectedBuyType ? "default" : "outline"}
								size="sm"
								aria-pressed={id === selectedBuyType}
							>
								{label}
							</Button>
						))}
					</div>
				</Section>

				{/* ----------------------------------------------------------------
				    4. 馬番入力（グリッド + テキスト）
				---------------------------------------------------------------- */}
				<Section title="馬番">
					<div className="space-y-6">
						{horseInputConfig.groups.map(({ key, label }) => (
							<HorseGrid
								key={key}
								groupKey={key}
								label={label}
								selectedHorses={STATIC_SELECTED_HORSES[key] ?? []}
							/>
						))}
					</div>
				</Section>

				{/* ----------------------------------------------------------------
				    5. 金額入力
				---------------------------------------------------------------- */}
				<Section title="金額">
					<div className="flex items-center gap-2">
						<Button type="button" variant="outline" size="icon" aria-label="100円減らす">
							−
						</Button>
						<Input
							type="number"
							min={100}
							step={100}
							defaultValue={100}
							className="w-28 text-center"
							aria-label="購入金額（円）"
						/>
						<span className="text-sm text-muted-foreground">円</span>
						<Button type="button" variant="outline" size="icon" aria-label="100円増やす">
							＋
						</Button>
						<div className="ml-4 flex gap-1.5">
							{[100, 500, 1000].map((preset) => (
								<Badge
									key={preset}
									variant="secondary"
									className="cursor-pointer select-none px-2 py-1 text-xs"
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
		</>
	);
}
