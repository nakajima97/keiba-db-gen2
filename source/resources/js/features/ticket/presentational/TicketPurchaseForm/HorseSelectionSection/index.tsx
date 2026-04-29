import { Button } from "@/components/shadcn/ui/button";
import { Label } from "@/components/shadcn/ui/label";
import { GRID_SIZE, HORSE_INPUT_CONFIG } from "../constants";
import { HorseGrid } from "../HorseGrid";
import { Section } from "../Section";
import { getHorseInputConfigKey } from "../utils";
import type { HorseSelectionSectionProps } from "./types";

export const HorseSelectionSection = ({
	selectedTicketTypeId,
	selectedBuyTypeId,
	selectedAxisCount,
	selectedNagashiDirection,
	selectedHorses,
	onAxisCountChange,
	onNagashiDirectionChange,
	onHorsesChange,
}: HorseSelectionSectionProps) => {
	const gridSize = GRID_SIZE[selectedTicketTypeId] ?? 18;

	const showAxisCountSelector =
		selectedBuyTypeId === "nagashi" && selectedTicketTypeId === "sanrenpuku";

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
		<Section title="馬番">
			<div className="space-y-6">
				{showAxisCountSelector && (
					<div className="space-y-2">
						<Label>軸の頭数</Label>
						<div className="flex gap-2">
							{([1, 2] as const).map((count) => (
								<Button
									key={count}
									type="button"
									variant={count === selectedAxisCount ? "default" : "outline"}
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
	);
};
