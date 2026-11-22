import { useEffect, useRef, useState } from "react";
import { toast } from "sonner";
import EntryCommentModal from "@/features/entryComment/presentational/EntryCommentModal";
import type { EntryCommentFormValues } from "@/features/entryComment/presentational/EntryCommentModal/types";
import {
	EntryCommentRequestError,
	type EntryCommentStats,
	createEntryComment,
	deleteEntryComment,
	updateEntryComment,
} from "@/features/entryComment/requests/entryComments";
import type {
	CommentOutcome,
	EntryComment,
} from "@/features/entryComment/types";
import {
	commentKey,
	replaceComment,
} from "@/features/entryComment/utils/commentKey";

const CONTENT_MAX_LENGTH = 1000;

type Props = {
	open: boolean;
	raceUid: string;
	raceEntryId: number;
	horseName: string;
	trainerName: string | null;
	jockeyName: string;
	comments: EntryComment[];
	onClose: () => void;
	onCommentsChange: (params: {
		raceEntryId: number;
		comments: EntryComment[];
		stats: EntryCommentStats;
	}) => void;
};

const buildInitialForm = (
	trainerName: string | null,
): EntryCommentFormValues => ({
	speakerType: trainerName !== null ? "trainer" : "jockey",
	sentiment: "positive",
	content: "",
	outcome: null,
});

const toErrorMessage = (e: unknown, fallback: string): string =>
	e instanceof EntryCommentRequestError && e.status === 422
		? e.message
		: fallback;

/**
 * 出走馬の調教師・騎手コメントモーダルのコンテナ。
 * フォーム state を保持し、追加・編集・削除・当否変更の API を呼んで、結果（コメント一覧と的中率）を親に通知する。
 * モーダルは保存後も開いたままにし、続けて入力できるようにする。
 */
const EntryCommentModalContainer = ({
	open,
	raceUid,
	raceEntryId,
	horseName,
	trainerName,
	jockeyName,
	comments,
	onClose,
	onCommentsChange,
}: Props) => {
	const [form, setForm] = useState<EntryCommentFormValues>(() =>
		buildInitialForm(trainerName),
	);
	const [editingKey, setEditingKey] = useState<string | null>(null);
	const [errorMessage, setErrorMessage] = useState<string | null>(null);
	const [submitting, setSubmitting] = useState<boolean>(false);
	const [pendingKeys, setPendingKeys] = useState<string[]>([]);

	const trainerNameRef = useRef(trainerName);
	trainerNameRef.current = trainerName;

	const previousOpenRef = useRef(false);
	useEffect(() => {
		const wasOpen = previousOpenRef.current;
		previousOpenRef.current = open;
		if (open && !wasOpen) {
			setForm(buildInitialForm(trainerNameRef.current));
			setEditingKey(null);
			setErrorMessage(null);
			setSubmitting(false);
			setPendingKeys([]);
		}
	}, [open]);

	const resetForm = () => {
		setForm(buildInitialForm(trainerName));
		setEditingKey(null);
		setErrorMessage(null);
	};

	const handleOpenChange = (next: boolean) => {
		if (!next) {
			onClose();
		}
	};

	const handleFormChange = (values: Partial<EntryCommentFormValues>) => {
		setForm((current) => ({ ...current, ...values }));
	};

	const handleSubmit = async () => {
		setErrorMessage(null);
		setSubmitting(true);
		const payload = {
			speaker_type: form.speakerType,
			sentiment: form.sentiment,
			content: form.content,
			outcome: form.outcome,
		};
		try {
			const editingComment =
				editingKey !== null
					? comments.find((c) => commentKey(c) === editingKey)
					: undefined;
			if (editingKey !== null && editingComment !== undefined) {
				const result = await updateEntryComment(
					raceUid,
					raceEntryId,
					editingComment,
					payload,
				);
				onCommentsChange({
					raceEntryId,
					comments: replaceComment(comments, editingKey, result.data),
					stats: result.stats,
				});
			} else {
				const result = await createEntryComment(raceUid, raceEntryId, payload);
				onCommentsChange({
					raceEntryId,
					comments: [...comments, result.data],
					stats: result.stats,
				});
			}
			resetForm();
		} catch (e) {
			const message = toErrorMessage(e, "コメントの保存に失敗しました");
			setErrorMessage(message);
			toast.error(message);
		} finally {
			setSubmitting(false);
		}
	};

	const withPending = async (
		comment: EntryComment,
		run: () => Promise<void>,
		failureMessage: string,
	) => {
		const key = commentKey(comment);
		setPendingKeys((current) => [...current, key]);
		try {
			await run();
		} catch (e) {
			toast.error(toErrorMessage(e, failureMessage));
		} finally {
			setPendingKeys((current) => current.filter((k) => k !== key));
		}
	};

	const handleOutcomeChange = (
		comment: EntryComment,
		outcome: CommentOutcome | null,
	) =>
		withPending(
			comment,
			async () => {
				const result = await updateEntryComment(raceUid, raceEntryId, comment, {
					outcome,
				});
				onCommentsChange({
					raceEntryId,
					comments: replaceComment(comments, commentKey(comment), result.data),
					stats: result.stats,
				});
			},
			"当否の保存に失敗しました",
		);

	const handleDeleteClick = (comment: EntryComment) =>
		withPending(
			comment,
			async () => {
				const stats = await deleteEntryComment(raceUid, raceEntryId, comment);
				const key = commentKey(comment);
				onCommentsChange({
					raceEntryId,
					comments: comments.filter((c) => commentKey(c) !== key),
					stats,
				});
				if (editingKey === key) {
					resetForm();
				}
			},
			"コメントの削除に失敗しました",
		);

	const handleEditClick = (comment: EntryComment) => {
		setEditingKey(commentKey(comment));
		setErrorMessage(null);
		setForm({
			speakerType: comment.speaker_type,
			sentiment: comment.sentiment,
			content: comment.content,
			outcome: comment.outcome,
		});
	};

	return (
		<EntryCommentModal
			open={open}
			horseName={horseName}
			trainerName={trainerName}
			jockeyName={jockeyName}
			comments={comments}
			editingKey={editingKey}
			form={form}
			contentMaxLength={CONTENT_MAX_LENGTH}
			errorMessage={errorMessage}
			submitting={submitting}
			pendingKeys={pendingKeys}
			onOpenChange={handleOpenChange}
			onFormChange={handleFormChange}
			onSubmit={handleSubmit}
			onEditClick={handleEditClick}
			onCancelEdit={resetForm}
			onDeleteClick={handleDeleteClick}
			onOutcomeChange={handleOutcomeChange}
		/>
	);
};

export default EntryCommentModalContainer;
