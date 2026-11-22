import { useEffect, useRef, useState } from "react";
import { toast } from "sonner";
import TrainerNoteModal from "@/features/trainerNote/presentational/TrainerNoteModal";
import type { TrainerNoteModalMode } from "@/features/trainerNote/presentational/TrainerNoteModal/types";
import {
	TrainerNoteRequestError,
	deleteTrainerNote,
	upsertTrainerNote,
} from "@/features/trainerNote/requests/trainerNotes";

const CONTENT_MAX_LENGTH = 1000;

type Props = {
	open: boolean;
	mode: TrainerNoteModalMode;
	trainerId: number;
	trainerName: string;
	trainerAffiliation: string | null;
	initialContent?: string;
	onClose: () => void;
	onSaved: (params: { trainerId: number; id: number; content: string }) => void;
	onDeleted: (params: { trainerId: number }) => void;
};

/**
 * 調教師メモの追加・編集モーダルのコンテナ。
 * 入力 state を保持し、送信時に upsert API、削除時に delete API を呼び出す。
 * 成功時は親に通知して閉じ、失敗時は errorMessage を表示しつつ toast でも通知する。
 */
const TrainerNoteModalContainer = ({
	open,
	mode,
	trainerId,
	trainerName,
	trainerAffiliation,
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
			const note = await upsertTrainerNote(trainerId, content);
			onSaved({ trainerId, id: note.id, content: note.content });
			onClose();
		} catch (e) {
			const message =
				e instanceof TrainerNoteRequestError
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
			await deleteTrainerNote(trainerId);
			onDeleted({ trainerId });
			onClose();
		} catch (e) {
			const message =
				e instanceof TrainerNoteRequestError
					? e.message
					: "メモの削除に失敗しました";
			setErrorMessage(message);
			toast.error(message);
		} finally {
			setSubmitting(false);
		}
	};

	return (
		<TrainerNoteModal
			open={open}
			mode={mode}
			trainerName={trainerName}
			trainerAffiliation={trainerAffiliation}
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

export default TrainerNoteModalContainer;
