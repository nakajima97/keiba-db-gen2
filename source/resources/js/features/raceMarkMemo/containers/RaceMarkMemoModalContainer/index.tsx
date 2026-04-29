import { useEffect, useRef, useState } from "react";
import { toast } from "sonner";
import RaceMarkMemoModal from "@/features/raceMarkMemo/presentational/RaceMarkMemoModal";
import type { RaceMarkMemoModalMode } from "@/features/raceMarkMemo/presentational/RaceMarkMemoModal/types";
import {
	RaceMarkMemoRequestError,
	deleteMemo,
	upsertMemo,
} from "@/features/raceMarkMemo/requests/raceMarkMemos";
import type { MarkValue } from "@/features/raceDetail/presentational/RaceDetail/types";

const CONTENT_MAX_LENGTH = 1000;

type Props = {
	open: boolean;
	mode: RaceMarkMemoModalMode;
	raceUid: string;
	columnId: number;
	raceEntryId: number;
	horseName: string;
	columnLabel: string;
	markValue: MarkValue | null;
	initialContent?: string;
	onClose: () => void;
	onSaved: (params: {
		columnId: number;
		raceEntryId: number;
		content: string;
	}) => void;
	onDeleted: (params: { columnId: number; raceEntryId: number }) => void;
};

/**
 * 印メモの追加・編集モーダルのコンテナ。
 * 入力 state を保持し、送信時に upsert API、削除時に delete API を呼び出す。
 * 成功時は親に通知して閉じ、失敗時は errorMessage を表示しつつ toast でも通知する。
 */
const RaceMarkMemoModalContainer = ({
	open,
	mode,
	raceUid,
	columnId,
	raceEntryId,
	horseName,
	columnLabel,
	markValue,
	initialContent = "",
	onClose,
	onSaved,
	onDeleted,
}: Props) => {
	const [content, setContent] = useState<string>(initialContent);
	const [errorMessage, setErrorMessage] = useState<string | null>(null);
	const [submitting, setSubmitting] = useState<boolean>(false);

	const initialContentRef = useRef(initialContent);
	initialContentRef.current = initialContent;

	const previousOpenRef = useRef(false);
	useEffect(() => {
		const wasOpen = previousOpenRef.current;
		previousOpenRef.current = open;
		if (open && !wasOpen) {
			setContent(initialContentRef.current);
			setErrorMessage(null);
			setSubmitting(false);
		}
	}, [open]);

	const handleOpenChange = (next: boolean) => {
		if (!next) {
			onClose();
		}
	};

	const handleSubmit = async () => {
		setErrorMessage(null);
		setSubmitting(true);
		try {
			const memo = await upsertMemo(raceUid, columnId, raceEntryId, content);
			onSaved({
				columnId,
				raceEntryId,
				content: memo.content,
			});
			onClose();
		} catch (e) {
			const message =
				e instanceof RaceMarkMemoRequestError
					? e.message
					: "メモの保存に失敗しました";
			setErrorMessage(message);
			toast.error(message);
		} finally {
			setSubmitting(false);
		}
	};

	const handleDelete = async () => {
		setErrorMessage(null);
		setSubmitting(true);
		try {
			await deleteMemo(raceUid, columnId, raceEntryId);
			onDeleted({ columnId, raceEntryId });
			onClose();
		} catch (e) {
			const message =
				e instanceof RaceMarkMemoRequestError
					? e.message
					: "メモの削除に失敗しました";
			setErrorMessage(message);
			toast.error(message);
		} finally {
			setSubmitting(false);
		}
	};

	return (
		<RaceMarkMemoModal
			open={open}
			mode={mode}
			horseName={horseName}
			columnLabel={columnLabel}
			markValue={markValue}
			content={content}
			contentMaxLength={CONTENT_MAX_LENGTH}
			errorMessage={errorMessage}
			submitting={submitting}
			onContentChange={setContent}
			onOpenChange={handleOpenChange}
			onSubmit={handleSubmit}
			onDelete={mode === "edit" ? handleDelete : undefined}
		/>
	);
};

export default RaceMarkMemoModalContainer;
