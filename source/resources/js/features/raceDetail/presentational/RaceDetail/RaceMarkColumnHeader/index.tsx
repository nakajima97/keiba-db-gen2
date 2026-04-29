import { X } from "lucide-react";
import { Button } from "@/components/shadcn/ui/button";
import { Input } from "@/components/shadcn/ui/input";
import type { RaceMarkColumn } from "../types";

type Props = {
	column: RaceMarkColumn;
	onChangeLabel: (label: string) => void;
	onRemove: () => void;
};

const RaceMarkColumnHeader = ({
	column,
	onChangeLabel,
	onRemove,
}: Props) => {
	if (column.type === "own") {
		return <span className="font-medium text-muted-foreground">自分</span>;
	}

	return (
		<div className="flex items-center gap-1">
			<Input
				value={column.label ?? ""}
				placeholder="ラベル"
				className="h-8 w-28"
				aria-label="他人の印列のラベル"
				onChange={(e) => onChangeLabel(e.target.value)}
			/>
			<Button
				type="button"
				variant="ghost"
				size="icon"
				className="h-8 w-8"
				aria-label="この印列を削除"
				onClick={onRemove}
			>
				<X className="h-4 w-4" />
			</Button>
		</div>
	);
};

export default RaceMarkColumnHeader;
