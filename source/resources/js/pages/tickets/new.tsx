import { JRA_PAYOUT_BASE_STAKE } from "@/constants/money";
import TicketPurchaseFormContainer from "@/features/ticket/containers/TicketPurchaseFormContainer";
import { Head, usePage } from "@inertiajs/react";

type TicketsNewProps = {
	lastVenue: string;
	lastRaceDate: string;
	lastRaceNumber: number;
};

const TicketsNew = () => {
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
				initialUnitStake={JRA_PAYOUT_BASE_STAKE}
			/>
		</>
	);
};

export default TicketsNew;
