import { Head } from "@inertiajs/react";
import TicketPurchaseFormContainer from "@/components/container/TicketPurchaseFormContainer";

export default function TicketsNew() {
	return (
		<>
			<Head title="馬券登録" />
			<TicketPurchaseFormContainer
				initialVenue="東京"
				initialRaceDate="2026-04-05"
				initialRaceNumber={1}
				initialTicketTypeId="umaren"
				initialBuyTypeId="nagashi"
				initialAxisCount={1}
				initialNagashiDirection={1}
				initialHorses={{ axis: [3], others: [1, 5, 7] }}
				initialAmount={100}
			/>
		</>
	);
}
