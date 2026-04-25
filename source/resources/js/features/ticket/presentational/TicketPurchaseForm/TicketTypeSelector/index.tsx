import { Button } from "@/components/shadcn/ui/button";
import { BUY_TYPE_MAP, TICKET_TYPES } from "../constants";
import { Section } from "../Section";
import type { TicketTypeSelectorProps } from "./types";

export function TicketTypeSelector({
	selectedTicketTypeId,
	selectedBuyTypeId,
	onTicketTypeChange,
	onBuyTypeChange,
}: TicketTypeSelectorProps) {
	const buyTypes = BUY_TYPE_MAP[selectedTicketTypeId];

	return (
		<>
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
		</>
	);
}
