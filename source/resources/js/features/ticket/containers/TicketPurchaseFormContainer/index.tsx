import TicketPurchaseForm from "@/features/ticket/presentational/TicketPurchaseForm";
import {
	BUY_TYPE_MAP,
	type TicketTypeId,
} from "@/features/ticket/presentational/TicketPurchaseForm";
import { useForm } from "@inertiajs/react";
import { useState } from "react";
import { toast } from "sonner";

export type TicketPurchaseFormContainerProps = {
	initialVenue: string;
	initialRaceDate: string;
	initialRaceNumber: number;
	initialTicketTypeId: TicketTypeId;
	initialBuyTypeId: string;
	initialAxisCount: 1 | 2;
	initialNagashiDirection: 1 | 2 | 3;
	initialHorses: Record<string, number[]>;
	initialUnitStake: number;
};

type TicketPurchaseFormData = {
	venue: string;
	race_date: string;
	race_number: number;
	ticket_type: TicketTypeId;
	buy_type: string;
	selections: Record<string, number[]>;
	unit_stake: number;
};

const TicketPurchaseFormContainer = ({
	initialVenue,
	initialRaceDate,
	initialRaceNumber,
	initialTicketTypeId,
	initialBuyTypeId,
	initialAxisCount,
	initialNagashiDirection,
	initialHorses,
	initialUnitStake,
}: TicketPurchaseFormContainerProps) => {
	const form = useForm<TicketPurchaseFormData>({
		venue: initialVenue,
		race_date: initialRaceDate,
		race_number: initialRaceNumber,
		ticket_type: initialTicketTypeId,
		buy_type: initialBuyTypeId,
		selections: initialHorses,
		unit_stake: initialUnitStake,
	});

	const [selectedAxisCount, setSelectedAxisCount] = useState<1 | 2>(
		initialAxisCount,
	);
	const [selectedNagashiDirection, setSelectedNagashiDirection] = useState<
		1 | 2 | 3
	>(initialNagashiDirection);

	const handleTicketTypeChange = (id: TicketTypeId) => {
		form.setData("ticket_type", id);
		form.setData("buy_type", BUY_TYPE_MAP[id][0].id);
		form.setData("selections", {});
	};

	const handleBuyTypeChange = (id: string) => {
		form.setData("buy_type", id);
		form.setData("selections", {});
	};

	const handleSubmit = (e: React.SyntheticEvent<HTMLFormElement>) => {
		e.preventDefault();

		form.post("/tickets", {
			onSuccess: () => {
				toast.success("馬券を登録しました");
				form.setData("selections", {});
			},
			onError: (errors) => {
				for (const message of Object.values(errors)) {
					toast.error(message);
				}
			},
		});
	};

	return (
		<form onSubmit={handleSubmit}>
			<TicketPurchaseForm
				selectedVenue={form.data.venue}
				selectedRaceDate={form.data.race_date}
				selectedRaceNumber={form.data.race_number}
				selectedTicketTypeId={form.data.ticket_type}
				selectedBuyTypeId={form.data.buy_type}
				selectedAxisCount={selectedAxisCount}
				selectedNagashiDirection={selectedNagashiDirection}
				selectedHorses={form.data.selections}
				unitStake={form.data.unit_stake}
				processing={form.processing}
				onVenueChange={(value) => form.setData("venue", value)}
				onRaceDateChange={(value) => form.setData("race_date", value)}
				onRaceNumberChange={(value) => form.setData("race_number", value)}
				onTicketTypeChange={handleTicketTypeChange}
				onBuyTypeChange={handleBuyTypeChange}
				onAxisCountChange={setSelectedAxisCount}
				onNagashiDirectionChange={setSelectedNagashiDirection}
				onHorsesChange={(groupKey, horses) =>
					form.setData("selections", {
						...form.data.selections,
						[groupKey]: horses,
					})
				}
				onUnitStakeChange={(value) => form.setData("unit_stake", value)}
			/>
		</form>
	);
};

export default TicketPurchaseFormContainer;
