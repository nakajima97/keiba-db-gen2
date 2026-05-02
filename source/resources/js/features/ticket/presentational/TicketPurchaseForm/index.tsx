import BackButton from "@/components/presentational/BackButton";
import { Button } from "@/components/shadcn/ui/button";
import { Input } from "@/components/shadcn/ui/input";
import { HorseSelectionSection } from "./HorseSelectionSection";
import { RaceInfoSection } from "./RaceInfoSection";
import { Section } from "./Section";
import { TicketTypeSelector } from "./TicketTypeSelector";
import type { TicketPurchaseFormProps } from "./types";

// テストファイルおよびStorybookから./indexを参照しているため、互換性のためにre-exportする
export { TICKET_TYPES, BUY_TYPE_MAP } from "./constants";
export type { TicketTypeId } from "./constants";
export { getHorseInputConfigKey } from "./utils";
export type { TicketPurchaseFormProps } from "./types";

const TicketPurchaseForm = ({
	selectedVenue,
	selectedRaceDate,
	selectedRaceNumber,
	selectedTicketTypeId,
	selectedBuyTypeId,
	selectedAxisCount,
	selectedNagashiDirection,
	selectedHorses,
	unitStake,
	processing,
	onVenueChange,
	onRaceDateChange,
	onRaceNumberChange,
	onTicketTypeChange,
	onBuyTypeChange,
	onAxisCountChange,
	onNagashiDirectionChange,
	onHorsesChange,
	onUnitStakeChange,
}: TicketPurchaseFormProps) => {
	return (
		<div className="mx-auto max-w-2xl space-y-8 p-4">
			<div>
				<BackButton label="戻る" />
			</div>

			<RaceInfoSection
				selectedVenue={selectedVenue}
				selectedRaceDate={selectedRaceDate}
				selectedRaceNumber={selectedRaceNumber}
				onVenueChange={onVenueChange}
				onRaceDateChange={onRaceDateChange}
				onRaceNumberChange={onRaceNumberChange}
			/>

			<TicketTypeSelector
				selectedTicketTypeId={selectedTicketTypeId}
				selectedBuyTypeId={selectedBuyTypeId}
				onTicketTypeChange={onTicketTypeChange}
				onBuyTypeChange={onBuyTypeChange}
			/>

			<HorseSelectionSection
				selectedTicketTypeId={selectedTicketTypeId}
				selectedBuyTypeId={selectedBuyTypeId}
				selectedAxisCount={selectedAxisCount}
				selectedNagashiDirection={selectedNagashiDirection}
				selectedHorses={selectedHorses}
				onAxisCountChange={onAxisCountChange}
				onNagashiDirectionChange={onNagashiDirectionChange}
				onHorsesChange={onHorsesChange}
			/>

			{/* 5. 金額入力 */}
			<Section title="金額">
				<div className="flex items-center gap-2">
					<Button
						type="button"
						variant="outline"
						size="icon"
						aria-label="100円減らす"
						onClick={() => onUnitStakeChange(Math.max(100, unitStake - 100))}
					>
						−
					</Button>
					<Input
						type="number"
						min={100}
						step={100}
						value={unitStake}
						className="w-28 text-center"
						aria-label="購入金額（円）"
						onChange={(e) => {
							const n = Number.parseInt(e.target.value, 10);
							if (n >= 100) onUnitStakeChange(n);
						}}
					/>
					<span className="text-sm text-muted-foreground">円</span>
					<Button
						type="button"
						variant="outline"
						size="icon"
						aria-label="100円増やす"
						onClick={() => onUnitStakeChange(unitStake + 100)}
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
								onClick={() => onUnitStakeChange(preset)}
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
};

export default TicketPurchaseForm;
