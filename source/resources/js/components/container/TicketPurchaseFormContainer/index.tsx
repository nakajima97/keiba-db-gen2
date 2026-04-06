import { useState } from "react";
import { router } from "@inertiajs/react";
import { toast } from "sonner";
import TicketPurchaseForm from "@/components/presentational/TicketPurchaseForm";
import {
	BUY_TYPE_MAP,
	type TicketTypeId,
} from "@/components/presentational/TicketPurchaseForm";

export type TicketPurchaseFormContainerProps = {
	initialVenue: string;
	initialRaceDate: string;
	initialRaceNumber: number;
	initialTicketTypeId: TicketTypeId;
	initialBuyTypeId: string;
	initialAxisCount: 1 | 2;
	initialNagashiDirection: 1 | 2 | 3;
	initialHorses: Record<string, number[]>;
	initialAmount: number;
};

export default function TicketPurchaseFormContainer({
	initialVenue,
	initialRaceDate,
	initialRaceNumber,
	initialTicketTypeId,
	initialBuyTypeId,
	initialAxisCount,
	initialNagashiDirection,
	initialHorses,
	initialAmount,
}: TicketPurchaseFormContainerProps) {
	const [selectedVenue, setSelectedVenue] = useState(initialVenue);
	const [selectedRaceDate, setSelectedRaceDate] = useState(initialRaceDate);
	const [selectedRaceNumber, setSelectedRaceNumber] = useState(initialRaceNumber);
	const [selectedTicketTypeId, setSelectedTicketTypeId] = useState<TicketTypeId>(initialTicketTypeId);
	const [selectedBuyTypeId, setSelectedBuyTypeId] = useState(initialBuyTypeId);
	const [selectedAxisCount, setSelectedAxisCount] = useState<1 | 2>(initialAxisCount);
	const [selectedNagashiDirection, setSelectedNagashiDirection] = useState<1 | 2 | 3>(initialNagashiDirection);
	const [selectedHorses, setSelectedHorses] = useState<Record<string, number[]>>(initialHorses);
	const [amount, setAmount] = useState(initialAmount);

	const handleTicketTypeChange = (id: TicketTypeId) => {
		setSelectedTicketTypeId(id);
		// 券種変更時は買い方を先頭にリセット、馬番もリセット
		setSelectedBuyTypeId(BUY_TYPE_MAP[id][0].id);
		setSelectedHorses({});
	};

	const handleBuyTypeChange = (id: string) => {
		setSelectedBuyTypeId(id);
		setSelectedHorses({});
	};

	const handleSubmit = (e: { preventDefault: () => void }) => {
		e.preventDefault();

		router.post(
			"/tickets",
			{
				venue: selectedVenue,
				race_date: selectedRaceDate,
				race_number: selectedRaceNumber,
				ticket_type: selectedTicketTypeId,
				buy_type: selectedBuyTypeId,
				selections: selectedHorses,
				amount,
			},
			{
				onSuccess: () => {
					toast.success("馬券を登録しました");
					setSelectedHorses({});
				},
			},
		);
	};

	return (
		<form onSubmit={handleSubmit}>
			<TicketPurchaseForm
				selectedVenue={selectedVenue}
				selectedRaceDate={selectedRaceDate}
				selectedRaceNumber={selectedRaceNumber}
				selectedTicketTypeId={selectedTicketTypeId}
				selectedBuyTypeId={selectedBuyTypeId}
				selectedAxisCount={selectedAxisCount}
				selectedNagashiDirection={selectedNagashiDirection}
				selectedHorses={selectedHorses}
				amount={amount}
				onVenueChange={setSelectedVenue}
				onRaceDateChange={setSelectedRaceDate}
				onRaceNumberChange={setSelectedRaceNumber}
				onTicketTypeChange={handleTicketTypeChange}
				onBuyTypeChange={handleBuyTypeChange}
				onAxisCountChange={setSelectedAxisCount}
				onNagashiDirectionChange={setSelectedNagashiDirection}
				onHorsesChange={(groupKey, horses) =>
					setSelectedHorses((prev) => ({ ...prev, [groupKey]: horses }))
				}
				onAmountChange={setAmount}
			/>
		</form>
	);
}
