import HorseNoteModal from "@/features/horseNote/presentational/HorseNoteModal";
import type {
	HorseNoteModalMode,
	HorseNoteRaceOption,
} from "@/features/horseNote/presentational/HorseNoteModal/types";
import {
	HorseNoteRequestError,
	createNote,
	updateNote,
} from "@/features/horseNote/requests/horseNotes";
import type { HorseNote } from "@/features/horseNote/types/horseNote";
import { useEffect, useRef, useState } from "react";
import { toast } from "sonner";

const CONTENT_MAX_LENGTH = 1000;

export type HorseNoteModalRaceContext =
	| { type: "fixed"; label: string }
	| {
			type: "selectable";
			options: HorseNoteRaceOption[];
			defaultUid?: string | null;
	  }
	| { type: "none" };

type Props = {
	open: boolean;
	mode: HorseNoteModalMode;
	horseId: number;
	horseName: string;
	noteId?: number;
	initialContent?: string;
	raceId?: number | null;
	raceContext: HorseNoteModalRaceContext;
	onClose: () => void;
	onSuccess: (note: HorseNote) => void;
};

/**
 * 競走馬メモの追加・編集モーダルのコンテナ。
 * 入力 state を保持し、送信時に API を呼び出す。
 * 成功時は onSuccess → onClose、失敗時は errorMessage を表示しつつ toast でも通知する。
 */
const HorseNoteModalContainer = ({
	open,
	mode,
	horseId,
	horseName,
	noteId,
	initialContent = "",
	raceId,
	raceContext,
	onClose,
	onSuccess,
}: Props) => {
	const [content, setContent] = useState<string>(initialContent);
	const [errorMessage, setErrorMessage] = useState<string | null>(null);
	const [submitting, setSubmitting] = useState<boolean>(false);
	const [selectedRaceUid, setSelectedRaceUid] = useState<string | null>(
		raceContext.type === "selectable" ? (raceContext.defaultUid ?? null) : null,
	);

	// open が false→true に切り替わった瞬間だけ state をリセットする。
	// open=true のまま親の再レンダリングで raceContext / initialContent の参照が変わっても
	// 入力中の内容を上書きしないように、ref で最新値を保持する。
	const initialContentRef = useRef(initialContent);
	const defaultRaceUidRef = useRef<string | null>(
		raceContext.type === "selectable" ? (raceContext.defaultUid ?? null) : null,
	);
	initialContentRef.current = initialContent;
	defaultRaceUidRef.current =
		raceContext.type === "selectable" ? (raceContext.defaultUid ?? null) : null;

	const previousOpenRef = useRef(false);
	useEffect(() => {
		const wasOpen = previousOpenRef.current;
		previousOpenRef.current = open;
		if (open && !wasOpen) {
			setContent(initialContentRef.current);
			setErrorMessage(null);
			setSubmitting(false);
			setSelectedRaceUid(defaultRaceUidRef.current);
		}
	}, [open]);

	const handleContentChange = (value: string) => {
		setContent(value);
	};

	const handleRaceSelect = (uid: string | null) => {
		setSelectedRaceUid(uid);
	};

	const handleOpenChange = (next: boolean) => {
		if (!next) {
			onClose();
		}
	};

	const handleSubmit = async () => {
		setErrorMessage(null);
		setSubmitting(true);
		try {
			let note: HorseNote;
			if (mode === "create") {
				const resolvedRaceId =
					raceContext.type === "selectable"
						? selectedRaceUid != null
							? (raceContext.options.find((o) => o.uid === selectedRaceUid)
									?.id ?? null)
							: null
						: (raceId ?? null);
				note = await createNote(horseId, resolvedRaceId, content);
			} else {
				if (noteId == null) {
					throw new Error("noteId is required for edit mode");
				}
				note = await updateNote(noteId, content);
			}
			onSuccess(note);
			onClose();
		} catch (e) {
			const message =
				e instanceof HorseNoteRequestError
					? e.message
					: "メモの保存に失敗しました";
			setErrorMessage(message);
			toast.error(message);
		} finally {
			setSubmitting(false);
		}
	};

	const modalRaceContext =
		raceContext.type === "selectable"
			? {
					type: "selectable" as const,
					options: raceContext.options,
					selectedUid: selectedRaceUid,
				}
			: raceContext.type === "fixed"
				? { type: "fixed" as const, label: raceContext.label }
				: { type: "none" as const };

	return (
		<HorseNoteModal
			open={open}
			mode={mode}
			horseName={horseName}
			content={content}
			contentMaxLength={CONTENT_MAX_LENGTH}
			errorMessage={errorMessage}
			submitting={submitting}
			raceContext={modalRaceContext}
			onContentChange={handleContentChange}
			onRaceSelect={handleRaceSelect}
			onOpenChange={handleOpenChange}
			onSubmit={handleSubmit}
		/>
	);
};

export default HorseNoteModalContainer;
