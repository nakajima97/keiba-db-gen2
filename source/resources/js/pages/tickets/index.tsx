import { Head, usePage, router } from "@inertiajs/react";
import { useState } from "react";
import TicketPurchaseList from "@/features/ticket/presentational/TicketPurchaseList";
import type { TicketPurchaseListItem } from "@/features/ticket/presentational/TicketPurchaseList";

type TicketsIndexProps = {
	purchases: TicketPurchaseListItem[];
	nextCursor: string | null;
};

const TicketsIndex = () => {
	const { purchases, nextCursor } = usePage<TicketsIndexProps>().props;
	const [isLoading, setIsLoading] = useState(false);

	const handleLoadMore = () => {
		setIsLoading(true);
		router.reload({
			only: ["purchases", "nextCursor"],
			data: { cursor: nextCursor },
			onFinish: () => setIsLoading(false),
			onError: () => setIsLoading(false),
		});
	};

	return (
		<>
			<Head title="購入馬券一覧" />
			<TicketPurchaseList
				purchases={purchases}
				hasMore={nextCursor !== null}
				isLoading={isLoading}
				onLoadMore={handleLoadMore}
			/>
		</>
	);
};

export default TicketsIndex;
