import { Input } from "@/components/shadcn/ui/input";
import { Label } from "@/components/shadcn/ui/label";

type HorseGridProps = {
	label: string;
	groupKey: string;
	gridSize: number;
	selectedHorses: number[];
	onToggle: (num: number) => void;
	onTextChange: (text: string) => void;
};

export function HorseGrid({
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
