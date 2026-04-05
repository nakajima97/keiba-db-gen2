import { Head } from "@inertiajs/react";
import TicketPurchaseForm from "@/components/presentational/TicketPurchaseForm";

export default function TicketsNew() {
	return (
		<>
			<Head title="馬券登録" />
			<TicketPurchaseForm
				selectedVenue="東京"
				selectedRaceDate="2026-04-05"
				selectedRaceNumber={1}
				selectedTicketTypeId="umaren"
				selectedBuyTypeId="nagashi"
				selectedAxisCount={1}
				selectedNagashiDirection={1}
				selectedHorses={{ axis: [3], others: [1, 5, 7] }}
				amount={100}
			/>
		</>
	);
}
