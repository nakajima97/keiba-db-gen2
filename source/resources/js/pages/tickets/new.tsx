import { Head, usePage } from "@inertiajs/react";
import TicketPurchaseFormContainer from "@/components/container/TicketPurchaseFormContainer";

type TicketsNewProps = {
	lastVenue: string;
	lastRaceDate: string;
	lastRaceNumber: number;
};

export default function TicketsNew() {
	const { lastVenue, lastRaceDate, lastRaceNumber } =
		usePage<TicketsNewProps>().props;

	return (
		<>
			<Head title="馬券登録" />
			<TicketPurchaseFormContainer
				initialVenue={lastVenue}
				initialRaceDate={lastRaceDate}
				initialRaceNumber={lastRaceNumber}
				initialTicketTypeId="umaren"
				initialBuyTypeId="nagashi"
				initialAxisCount={1}
				initialNagashiDirection={1}
				initialHorses={{}}
				initialAmount={100}
			/>
		</>
	);
}
