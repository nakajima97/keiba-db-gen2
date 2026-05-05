import TicketPurchaseDeleteConfirmDialog from "@/features/ticket/presentational/TicketPurchaseDeleteConfirmDialog";
import TicketPurchaseList from "@/features/ticket/presentational/TicketPurchaseList";
import type { TicketPurchaseListItem } from "@/features/ticket/presentational/TicketPurchaseList";
import { Head, router, usePage } from "@inertiajs/react";
import { useState } from "react";

type TicketsIndexProps = {
	purchases: TicketPurchaseListItem[];
	nextCursor: string | null;
};

const TicketsIndex = () => {
	const { purchases, nextCursor } = usePage<TicketsIndexProps>().props;
	const [isLoading, setIsLoading] = useState(false);
	const [selectedPurchase, setSelectedPurchase] =
		useState<TicketPurchaseListItem | null>(null);
	const [isSubmitting, setIsSubmitting] = useState(false);
	const [errorMessage, setErrorMessage] = useState<string | null>(null);

	const handleLoadMore = () => {
		setIsLoading(true);
		router.reload({
			only: ["purchases", "nextCursor"],
			data: { cursor: nextCursor },
			onFinish: () => setIsLoading(false),
			onError: () => setIsLoading(false),
		});
	};

	const handleDeleteClick = (id: number) => {
		const purchase = purchases.find((p) => p.id === id) ?? null;
		setSelectedPurchase(purchase);
		setErrorMessage(null);
	};

	const handleDialogOpenChange = (open: boolean) => {
		if (!open) {
			setSelectedPurchase(null);
			setErrorMessage(null);
		}
	};

	const handleConfirmDelete = () => {
		if (selectedPurchase === null) {
			return;
		}

		setIsSubmitting(true);
		setErrorMessage(null);

		router.delete(`/tickets/${selectedPurchase.id}`, {
			onSuccess: () => {
				setSelectedPurchase(null);
			},
			onError: () => {
				setErrorMessage("削除に失敗しました。時間をおいて再度お試しください。");
			},
			onFinish: () => {
				setIsSubmitting(false);
			},
		});
	};

	const ticketSummary = selectedPurchase
		? {
				raceDate: selectedPurchase.race_date,
				venueName: selectedPurchase.venue_name,
				raceNumber: selectedPurchase.race_number,
				ticketTypeLabel: selectedPurchase.ticket_type_label,
			}
		: null;

	return (
		<>
			<Head title="購入馬券一覧" />
			<TicketPurchaseList
				purchases={purchases}
				hasMore={nextCursor !== null}
				isLoading={isLoading}
				onLoadMore={handleLoadMore}
				onDelete={handleDeleteClick}
			/>
			<TicketPurchaseDeleteConfirmDialog
				open={selectedPurchase !== null}
				ticketSummary={ticketSummary}
				submitting={isSubmitting}
				errorMessage={errorMessage}
				onOpenChange={handleDialogOpenChange}
				onConfirm={handleConfirmDelete}
			/>
		</>
	);
};

export default TicketsIndex;
