import { useMemo, useState } from "react";
import type { EntryCommentStats } from "@/features/entryComment/requests/entryComments";
import type { EntryComment } from "@/features/entryComment/types";
import type { RaceEntry } from "@/features/raceDetail/presentational/RaceDetail/types";
import { applyEntryCommentsChange } from "@/features/raceDetail/utils/applyEntryCommentsChange";

type EntryCommentModalState = {
	open: boolean;
	raceEntryId: number | null;
};

type UseEntryCommentsReturn = {
	commentModal: EntryCommentModalState;
	commentTargetEntry: RaceEntry | undefined;
	handleCommentClick: (raceEntryId: number) => void;
	handleCommentClose: () => void;
	handleCommentsChange: (params: {
		raceEntryId: number;
		comments: EntryComment[];
		stats: EntryCommentStats;
	}) => void;
};

/**
 * レース詳細画面の調教師・騎手コメントモーダルの開閉 state と、変更後の出走馬 state 同期を管理する。
 * 的中率は全レース分の集計なので、同じ調教師・騎手の出走馬すべてに API が返した stats を反映する。
 * API 呼び出しは EntryCommentModalContainer 側が担う。
 */
export const useEntryComments = (
	entries: RaceEntry[],
	setEntries: (updater: (current: RaceEntry[]) => RaceEntry[]) => void,
): UseEntryCommentsReturn => {
	const [commentModal, setCommentModal] = useState<EntryCommentModalState>({
		open: false,
		raceEntryId: null,
	});

	const handleCommentClick = (raceEntryId: number) => {
		setCommentModal({ open: true, raceEntryId });
	};

	const handleCommentClose = () => {
		setCommentModal((current) => ({ ...current, open: false }));
	};

	const handleCommentsChange = ({
		raceEntryId,
		comments,
		stats,
	}: {
		raceEntryId: number;
		comments: EntryComment[];
		stats: EntryCommentStats;
	}) => {
		setEntries((current) =>
			applyEntryCommentsChange(current, { raceEntryId, comments, stats }),
		);
	};

	const commentTargetEntry = useMemo(
		() =>
			commentModal.raceEntryId != null
				? entries.find((e) => e.id === commentModal.raceEntryId)
				: undefined,
		[entries, commentModal.raceEntryId],
	);

	return {
		commentModal,
		commentTargetEntry,
		handleCommentClick,
		handleCommentClose,
		handleCommentsChange,
	};
};
