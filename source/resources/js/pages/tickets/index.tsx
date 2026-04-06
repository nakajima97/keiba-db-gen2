import { Head } from "@inertiajs/react";
import TicketPurchaseList from "@/components/presentational/TicketPurchaseList";
import type { TicketPurchaseListItem } from "@/components/presentational/TicketPurchaseList";

type TicketsIndexProps = {
	purchases: TicketPurchaseListItem[];
	nextCursor: string | null;
};

export default function TicketsIndex() {
	return (
		<>
			<Head title="購入馬券一覧" />
			<TicketPurchaseList
				purchases={[]}
				hasMore={false}
				isLoading={false}
				onLoadMore={() => {}}
			/>
		</>
	);
}
